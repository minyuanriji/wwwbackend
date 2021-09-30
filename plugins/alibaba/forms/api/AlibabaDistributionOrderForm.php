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
use lin010\alibaba\c2b2b\api\OrderCreate;
use lin010\alibaba\c2b2b\api\OrderCreateResponse;
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
     * 使用购物券抵扣运费的比例
     * @return float
     */
    public static function getShoppingVoucherDecodeExpressRate(){
        return 0.1;
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

            $goodsItem = [
                "id"          => $goods->id,
                "mall_id"     => $goods->mall_id,
                "app_id"      => $goods->app_id,
                "name"        => $goods->name,
                "cover_url"   => $goods->cover_url,
                "ali_offerId" => $goods->ali_offerId,
                "num"         => max(1, (int)$item['num'])
            ];

            $skuInfos = @json_decode($goods->sku_infos, true);
            if((empty($skuInfos) || empty($skuInfos['group'])) && $item['sku'] == "DEF"){ //无规格商品
                $goodsItem['ali_sku']       = 0;
                $goodsItem['ali_num']       = 1;
                $goodsItem['ali_spec_id']   = '';
                $goodsItem['price']         = (float)$goods->price;
                $goodsItem['freight_price'] = (float)$goods->freight_price;
                $goodsItem['sku_id']        = 0;
                $goodsItem['sku_labels']    = ['默认规格'];
            }else{
                $sku = AlibabaDistributionGoodsSku::findOne($item['sku']);
                if(!$sku || $sku->is_delete || $sku->goods_id != $goods->id){
                    throw new \Exception("规格[ID:".$item['sku']."]不存在");
                }

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
                $goodsItem['ali_sku']       = $sku->ali_sku_id;
                $goodsItem['ali_num']       = $sku->ali_num;
                $goodsItem['ali_spec_id']   = $sku->ali_spec_id;
                $goodsItem['price']         = (float)$sku->price;
                $goodsItem['freight_price'] = (float)$sku->freight_price;
                $goodsItem['sku_id']        = $sku->id;
                $goodsItem['sku_labels']    = !empty($sku->name) ? [$sku->name] : $labels;
            }

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
                'quantity'  => $orderDetail->ali_num
            ]),
            "outerOrderInfo" => json_encode([
                "mediaOrderId" => $orderDetail->id,
                "phone"        => $userAddress->mobile,
                "offers"       => [
                    "id"     => $goods->ali_offerId,
                    "specId" => $orderDetail->ali_spec_id,
                    "price"  => $orderDetail->unit_price * 100,
                    "num"    => $orderDetail->ali_num
                ]
            ]),
            "message" => "发货不要放任何单据，有任何问题先不发货，不打客户收货电话，打这个电话13536992449/020-31923526"
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
        if($this->use_address_id){
            $userAddress = UserAddress::findOne($this->use_address_id);
        }else{
            $userAddress = UserAddress::getUserAddressDefault([
                'user_id'   => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);
        }

        //计算运费
        $orderItem['express_price'] = 0;
        foreach($orderItem["goods_list"] as &$goodsItem){
            $orderItem['express_price'] = max($goodsItem['freight_price'], $orderItem['express_price']);
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
            $orderItem['if_shopping_voucher_need_total_num'] = 0;
            $orderItem['shopping_voucher_use_num'] = 0;
            $orderItem['shopping_voucher_decode_price'] = 0;
            foreach($orderItem['goods_list'] as &$goodsItem){
                $goodsItem['if_shopping_voucher_need_total_num'] = 0;
                $goodsItem['use_shopping_voucher'] = 0;
                if($this->use_shopping_voucher && $goodsItem['total_price'] > 0){
                    $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                        "goods_id"  => $goodsItem['id'],
                        "sku_id"    => (int)$goodsItem['sku_id'],
                        "is_delete" => 0
                    ]);
                    if(!$voucherGoods) continue;
                    $goodsItem['use_shopping_voucher'] = 1;
                    //计算购物券价与商品价格比例
                    $ratio = $voucherGoods->voucher_price/$goodsItem['price'];
                    $goodsItem['if_shopping_voucher_need_total_num'] = floatval($goodsItem['total_price']) * $ratio;
                    if(($userRemainingShoppingVoucher/$ratio) > $goodsItem['total_price']){
                        $needNum = $goodsItem['if_shopping_voucher_need_total_num'];
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
                $orderItem['if_shopping_voucher_need_total_num'] += $goodsItem['if_shopping_voucher_need_total_num'];
            }
            $orderItem['total_price'] -= round($orderItem['shopping_voucher_decode_price'], 2);

            //运费抵扣
            $orderItem['shopping_voucher_express_decode_price'] = 0;
            $orderItem['shopping_voucher_express_use_num'] = 0;
            if($this->use_shopping_voucher && $orderItem['express_price'] > 0){
                $ratio = static::getShoppingVoucherDecodeExpressRate(); //运费抵扣比例
                $expressNeedTotalNum = $orderItem['express_price'] * (1/$ratio);
                if($userRemainingShoppingVoucher > $expressNeedTotalNum){ //可全部抵扣运费
                    $orderItem['shopping_voucher_express_use_num'] = $expressNeedTotalNum;
                    $userRemainingShoppingVoucher -= $expressNeedTotalNum;
                    $orderItem['shopping_voucher_express_decode_price'] = $orderItem['express_price'];
                    $orderItem['express_price'] = 0;
                }else{ //部分抵扣运费
                    $orderItem['shopping_voucher_express_decode_price'] = $ratio * $userRemainingShoppingVoucher;
                    $orderItem['express_price'] -= $orderItem['shopping_voucher_express_decode_price'];
                    $orderItem['shopping_voucher_express_use_num'] = $userRemainingShoppingVoucher;
                    $userRemainingShoppingVoucher = 0;
                }
                $orderItem['if_shopping_voucher_need_total_num'] += $expressNeedTotalNum;
            }
            $orderItem['total_price'] -= $orderItem['shopping_voucher_express_decode_price'];
        }

        $mainData['shopping_voucher']['use_num'] = round($mainData['shopping_voucher']['use_num'], 2);
        $mainData['shopping_voucher']['remaining'] = round($userRemainingShoppingVoucher, 2);

        //统计订单待支付总金额
        foreach($mainData['list'] as $orderItem){
            $mainData['shopping_voucher']['decode_price'] += $orderItem['shopping_voucher_decode_price'];
            $mainData['shopping_voucher']['decode_price'] += $orderItem['shopping_voucher_express_decode_price'];
            $mainData['shopping_voucher']['use_num'] += $orderItem['shopping_voucher_use_num'];
            $mainData['shopping_voucher']['use_num'] += $orderItem['shopping_voucher_express_use_num'];
            $mainData['total_price'] += $orderItem['total_price'];
            $orderItem['total_goods_original_price'] = round($orderItem['total_goods_original_price'], 2);
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