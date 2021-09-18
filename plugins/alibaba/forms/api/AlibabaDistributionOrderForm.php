<?php

namespace app\plugins\alibaba\forms\api;

use app\models\BaseModel;
use app\models\UserAddress;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;
use lin010\alibaba\c2b2b\api\GetAddress;
use lin010\alibaba\c2b2b\api\GetAddressResponse;
use lin010\alibaba\c2b2b\api\OrderCreate;
use lin010\alibaba\c2b2b\api\OrderCreateResponse;
use lin010\alibaba\c2b2b\api\OrderGetPreview;
use lin010\alibaba\c2b2b\api\OrderGetPreviewResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionOrderForm extends BaseModel{

    public $list; //结构[{"goods":57, "sku":11, "num":1}, ...]
    public $use_shopping_voucher;
    public $use_address_id;
    public $remark;

    public function rules(){
        return [
            [['list'], 'required'],
            [['use_address_id'], 'integer'],
            [['use_shopping_voucher'], 'number', 'min' => 0],
            [['remark'], 'safe']
        ];
    }

    /**
     * 获取商品列表信息
     * @return array
     * @throws \Exception
     */
    protected function getGoodsList(){
        $list = @json_decode($this->list, true);
        if(!$list || !is_array($list)){
            throw new \Exception("参数“list”格式不正确");
        }
        $goodsList = [];
        foreach($list as $item){
            $goods = AlibabaDistributionGoodsList::findOne($item['goods']);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品[ID:".$item['goods']."]已下架或不存在");
            }
            $sku = AlibabaDistributionGoodsSku::findOne($item['sku']);
            if(!$sku || $sku->is_delete || $sku->goods_id != $goods->id){
                throw new \Exception("规格[ID:".$item['sku']."]不存在");
            }
            $skuInfos = json_decode($goods->sku_infos, true);
            $attrs = explode(",", $sku->ali_attributes);
            $labels = [];
            foreach($attrs as $attr){
                $part = explode(":", $attr);
                if(isset($skuInfos['group'][$part[0]]) && isset($skuInfos['values'][$attr])){
                    $labels[] = $skuInfos['group'][$part[0]]['attributeName'] . ":" . $skuInfos['values'][$attr];
                }else{
                    $labels[] = "-:-";
                }
            }
            $goodsItem = [
                "id"          => $goods->id,
                "mall_id"     => $goods->mall_id,
                "app_id"      => $goods->app_id,
                "name"        => $goods->name,
                "cover_url"   => $goods->cover_url,
                "ali_offerId" => $goods->ali_offerId,
                "price"       => (float)$sku->price,
                "num"         => max(1, (int)$item['num']),
                "sku_id"      => $sku->id,
                "ali_sku"     => $sku->ali_sku_id,
                "ali_spec_id" => $sku->ali_spec_id,
                "sku_labels"  => $labels
            ];
            $goodsItem['total_original_price'] = $goodsItem['num'] * $goodsItem['price'];
            $goodsItem['total_price'] = $goodsItem['total_original_price'];
            $goodsList[] = $goodsItem;
        }
        return $goodsList;
    }

    /**
     * 创建1688订单
     * @param AlibabaDistributionOrder $order
     * @param AlibabaDistributionOrderDetail $orderDetail
     * @param UserAddress $userAddress
     * @throws \Exception
     */
    public static function createAliOrder(AlibabaDistributionOrder $order, AlibabaDistributionOrderDetail $orderDetail, UserAddress $userAddress){
        static $appList;
        if(!isset($appList[$orderDetail->app_id])){
            $appList[$orderDetail->app_id] = AlibabaApp::findOne($orderDetail->app_id);
            if(!$appList[$orderDetail->app_id] || $appList[$orderDetail->app_id]->is_delete){
                throw new \Exception("应用信息[ID:{$orderDetail->app_id}]不存在");
            }
        }
        $app = $appList[$orderDetail->app_id];

        $goods = AlibabaDistributionGoodsList::findOne($orderDetail->goods_id);
        if(!$goods || $goods->is_delete){
            throw new \Exception("商品[ID:{$orderDetail->goods_id}]不存在");
        }

        $aliAddrInfo = (array)@json_decode($order->ali_address_info, true);

        $distribution = new Distribution($app->app_key, $app->secret);

        $postData = [
            "addressParam" => json_encode([
                "fullName"     => $userAddress->name,
                "mobile"       => $userAddress->mobile,
                "phone"        => $userAddress->mobile,
                "postCode"     => isset($aliAddrInfo['postCode']) ? $aliAddrInfo['postCode'] : "",
                "cityText"     => $userAddress->city,
                "provinceText" => $userAddress->province,
                "areaText"     => $userAddress->district,
                "address"      => $userAddress->detail,
                "districtCode" => isset($aliAddrInfo['addressCode']) ? $aliAddrInfo['addressCode'] : ""
            ]),
            "cargoParamList" => json_encode([
                'offerId'   => $goods->ali_offerId,
                'specId'    => $orderDetail->ali_spec_id,
                'quantity'  => $orderDetail->num
            ]),
            "outerOrderInfo" => json_encode([
                "mediaOrderId" => $orderDetail->id,
                "phone"        => $userAddress->mobile,
                "offers"       => [
                    "id"     => $goods->ali_offerId,
                    "specId" => $orderDetail->ali_spec_id,
                    "price"  => $orderDetail->unit_price * 100,
                    "num"    => $orderDetail->num
                ]
            ])
        ];
        $res = $distribution->requestWithToken(new OrderCreate($postData), $app->access_token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof OrderCreateResponse){
            throw new \Exception("返回结果异常");
        }
        $orderDetail1688 = new AlibabaDistributionOrderDetail1688([
            "mall_id"          => $order->mall_id,
            "app_id"           => $app->id,
            "order_id"         => $order->id,
            "order_detail_id"  => $orderDetail->id,
            "goods_id"         => $goods->id,
            "user_id"          => $order->user_id,
            "ali_total_amount" => $res->totalSuccessAmount,
            "ali_order_id"     => $res->orderId,
            "ali_post_fee"     => $res->postFee,
            "ali_postdata"     => json_encode($postData),
            "created_at"       => time(),
            "updated_at"       => time(),
            "app_key"          => $app->app_key,
            "status"           => "unpaid"
        ]);
        if(!$orderDetail1688->save()){
            throw new \Exception(json_encode($orderDetail1688->getErrors()));
        }
    }


    /**
     * 阿里巴巴订单预览接口
     * @param Distribution $distribution
     * @param string $token
     * @param array $cargoParamList 商品信息 [['offerId' => 1, 'specId' => 'xxx', 'quantity' => 1]]
     * @param UserAddress $userAddress
     * @param array $aliAddrInfo
     * @return array
     * @throws \Exception
     */
    public static function getAliOrderPreviewData(Distribution $distribution, $token, $cargoParamList, UserAddress $userAddress, $aliAddrInfo){
        $res = $distribution->requestWithToken(new OrderGetPreview([
            "addressParam" => json_encode([
                "fullName"     => $userAddress->name,
                "mobile"       => $userAddress->mobile,
                "phone"        => $userAddress->mobile,
                "postCode"     => isset($aliAddrInfo['postCode']) ? $aliAddrInfo['postCode'] : "",
                "cityText"     => $userAddress->city,
                "provinceText" => $userAddress->province,
                "areaText"     => $userAddress->district,
                "address"      => $userAddress->detail,
                "districtCode" => isset($aliAddrInfo['addressCode']) ? $aliAddrInfo['addressCode'] : ""
            ]),
            "cargoParamList" => json_encode($cargoParamList)
        ]), $token);
        if(!empty($res->error)){
            throw new \Exception($res->error);
        }
        if(!$res instanceof OrderGetPreviewResponse){
            throw new \Exception("返回结果异常");
        }
        return $res->result;
    }

    /**
     * 获取订单数据
     * @return array
     * @throws \Exception
     */
    protected function getData(){
        $mainData = [];

        $goodsList = $this->getGoodsList();
        if(!$goodsList){
            throw new \Exception("商品不能为空");
        }

        $orderItem = [];
        $orderItem["goods_list"] = $goodsList;

        //快递运费
        $userAddress = [];
        if($this->use_address_id){
            $userAddress = UserAddress::findOne($this->use_address_id);
        }else{
            $userAddress = UserAddress::getUserAddressDefault([
                'user_id'   => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);
        }

        //获取第一个商品的应用ID
        $app = AlibabaApp::findOne($goodsList[0]['app_id']);
        $distribution = new Distribution($app->app_key, $app->secret);


        $mainData['ali_address_info'] = [];
        if($userAddress){
            $mainData['user_address'] = $userAddress->getAttributes();
            //解析1688的地址
            $res = $distribution->requestWithToken(new GetAddress([
                "addressInfo" => "{$userAddress->province} {$userAddress->city} {$userAddress->district}{$userAddress->detail}"
            ]), $app->access_token);
            if(!empty($res->error)){
                throw new \Exception($res->error);
            }
            if($res instanceof GetAddressResponse){
                $mainData['ali_address_info'] = $res->result;
            }
        }

        //调用阿里巴巴订单预览接口计算运费
        $orderItem['express_price'] = 0;
        if($mainData['ali_address_info']){
            foreach($orderItem["goods_list"] as &$goodsItem){
                $aliPreviewData = static::getAliOrderPreviewData($distribution, $app->access_token, [
                    'offerId'   => $goodsItem['ali_offerId'],
                    'specId'    => $goodsItem['ali_spec_id'],
                    'quantity'  => $goodsItem['num']
                ], $userAddress,  $mainData['ali_address_info']);
                foreach($aliPreviewData as $previewData){
                    $orderItem['express_price'] += floatval($previewData['sumCarriage']/100);
                }
            }
        }
        $orderItem['express_origin_price'] = $orderItem['express_price'];

        //计算订单商品初始总金额
        $orderItem['total_price'] = 0;
        foreach($goodsList as $goodsItem){
            $orderItem['total_price'] += floatval($goodsItem['price'] * $goodsItem['num']);
        }
        $orderItem['total_goods_original_price'] = $orderItem['total_price'];
        $orderItem['total_price'] += $orderItem['express_price'];

        //订单数据
        $mainData['total_price'] = 0;
        $mainData["remark"] = $this->remark;
        $mainData['list'] = [$orderItem];
        $mainData['user_address_enable'] = true;
        $mainData['user_address'] = $userAddress;

        //使用购物券
        $userRemainingShoppingVoucher = (float)ShoppingVoucherUser::find()->where([
            "user_id" => \Yii::$app->user->id
        ])->select("money")->scalar();
        $mainData['shopping_voucher'] = [
            "decode_price" => 0,
            "enable"       => true,
            "remaining"    => $userRemainingShoppingVoucher,
            "total"        => $userRemainingShoppingVoucher,
            "use"          => $this->use_shopping_voucher ? true : false,
            "use_num"      => 0
        ];
        foreach($mainData['list'] as &$orderItem){
            $orderItem['shopping_voucher_use_num'] = 0;
            $orderItem['shopping_voucher_decode_price'] = 0;
            foreach($orderItem['goods_list'] as &$goodsItem){
                $goodsItem['use_shopping_voucher'] = 0;
                if($this->use_shopping_voucher && $goodsItem['total_price'] > 0){
                    $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                        "goods_id"  => $goodsItem['id'],
                        "is_delete" => 0
                    ]);
                    if(!$voucherGoods) continue;
                    $goodsItem['use_shopping_voucher'] = 1;
                    //计算购物券价与商品价格比例
                    $ratio = $voucherGoods->voucher_price/$goodsItem['price'];
                    if(($userRemainingShoppingVoucher/$ratio) > $goodsItem['total_price']){
                        $needNum = floatval($goodsItem['total_price']) * $ratio;
                        $goodsItem['use_shopping_voucher_decode_price'] = $goodsItem['total_price'];
                        $userRemainingShoppingVoucher -= $needNum;
                        $goodsItem['use_shopping_voucher_num'] = $needNum;
                        $goodsItem['total_price'] = 0;
                    }else{
                        $decodePrice = round(($userRemainingShoppingVoucher/$ratio), 2);
                        $goodsItem['total_price'] -= $decodePrice;
                        $goodsItem['use_shopping_voucher_decode_price'] = $decodePrice;
                        $goodsItem['use_shopping_voucher_num'] = $userRemainingShoppingVoucher;
                        $userRemainingShoppingVoucher = 0;
                    }
                    $orderItem['shopping_voucher_decode_price'] += $goodsItem['use_shopping_voucher_decode_price'];
                    $orderItem['shopping_voucher_use_num'] += $goodsItem['use_shopping_voucher_num'];
                }
            }
            $orderItem['total_price'] -= round($orderItem['shopping_voucher_decode_price'], 2);

            //运费抵扣
            $orderItem['shopping_voucher_express_decode_price'] = 0;
            $orderItem['shopping_voucher_express_use_num'] = 0;
            if($this->use_shopping_voucher && $orderItem['express_price'] > 0){
                $ratio = 1; //运费抵扣比例
                $expressNeedTotalNum = $orderItem['express_price'] * (1/$ratio);
                if($userRemainingShoppingVoucher > $expressNeedTotalNum){ //可全部抵扣运费
                    $orderItem['shopping_voucher_express_use_num'] = $expressNeedTotalNum;
                    $userRemainingShoppingVoucher -= $expressNeedTotalNum;
                    $orderItem['shopping_voucher_express_decode_price'] = $orderItem['express_price'];
                    $orderItem['express_price'] = 0;
                }else{ //部分抵扣运费
                    $orderItem['shopping_voucher_express_decode_price'] = $ratio * $userRemainingShoppingVoucher;
                    $orderItem['express_price'] -= $orderItem['shopping_voucher_express_decode_price'];
                    $userRemainingShoppingVoucher = 0;
                }
            }
            $orderItem['total_price'] -= $orderItem['shopping_voucher_express_decode_price'];
        }

        $mainData['shopping_voucher']['remaining'] = $userRemainingShoppingVoucher;

        //统计订单待支付总金额
        foreach($mainData['list'] as $orderItem){
            $mainData['shopping_voucher']['decode_price'] += $orderItem['shopping_voucher_decode_price'];
            $mainData['shopping_voucher']['decode_price'] += $orderItem['shopping_voucher_express_decode_price'];
            $mainData['shopping_voucher']['use_num'] += $orderItem['shopping_voucher_use_num'];
            $mainData['shopping_voucher']['use_num'] += $orderItem['shopping_voucher_express_use_num'];
            $mainData['total_price'] += $orderItem['total_price'];
        }
        $mainData['total_price'] = round($mainData['total_price'], 2);

        return $mainData;
    }

    /**
     * 生成订单号
     * @return string
     */
    public static function getOrderNo(){
        return generate_order_no("ALIS");
    }
}