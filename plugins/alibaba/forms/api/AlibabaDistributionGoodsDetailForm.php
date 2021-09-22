<?php

namespace app\plugins\alibaba\forms\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\UserAddress;
use app\plugins\alibaba\models\AlibabaApp;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;
use lin010\alibaba\c2b2b\api\GetAddress;
use lin010\alibaba\c2b2b\api\GetAddressResponse;
use lin010\alibaba\c2b2b\Distribution;

class AlibabaDistributionGoodsDetailForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $user_id;
    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['mall_id', 'user_id', 'id'], 'integer']
        ];
    }

    /**
     * 获取购物券抵扣价格
     * @param AlibabaDistributionGoodsList $goods
     * @param integer $skuId 规格ID
     * @param string $spec 阿里巴巴规格
     * @param float $price 价格
     * @param integer $num 数量
     * @return float 如果返回-1不支持购物券抵扣
     */
    public static function getShoppingVoucherDecodeNeedNumber(AlibabaDistributionGoodsList $goods, $skuId, $spec, $price, $num){
        static $voucherGoodsList = null, $expressPrice = 0;
        if($voucherGoodsList === null){
            $rows = ShoppingVoucherTargetAlibabaDistributionGoods::find()->where([
                "goods_id"  => $goods->id,
                "is_delete" => 0
            ])->select(["goods_id", "sku_id", "voucher_price"])->asArray()->all();
            $voucherGoodsList = [];
            foreach($rows as $row){
                $voucherGoodsList[$goods->id . ":" . $row['sku_id']] = $row['voucher_price'];
            }
        }

        $number = -1; //需要的购物券数量
        if($price > 0 && isset($voucherGoodsList[$goods->id . ":" . $skuId])){
            $voucherPrice = $voucherGoodsList[$goods->id . ":" . $skuId];
            $ratio = $voucherPrice/$price; //购物券价与商品价格比例
            $number = floatval($price) * $ratio * $num;

            //获取用户默认的收货地址
            $userAddress = UserAddress::getUserAddressDefault([
                'user_id'   => \Yii::$app->user->id,
                'is_delete' => 0,
            ]);
            if(!$userAddress){ //如果获取不到用户收货地址，使用定位地址
                if(!empty(ApiController::$commonData['city_data']['province'])){
                    $addrInfo = [
                        "province" => ApiController::$commonData['city_data']['province'],
                        "city"     => ApiController::$commonData['city_data']['city'],
                        "district" => ApiController::$commonData['city_data']['district'],
                        "mobile"   => "",
                        "detail"   => ApiController::$commonData['city_data']['street'],
                    ];
                }else{
                    $addrInfo = [
                        "province" => "广东省",
                        "city"     => "广州市",
                        "district" => "白云区",
                        "mobile"   => "",
                        "detail"   => "鹤龙一路",
                    ];
                }
                $userAddress = new UserAddress();
                $userAddress->load(["UserAddress" => array_merge([
                    "user_id" => \Yii::$app->user->id,
                    "name"    => "用户" . \Yii::$app->user->id
                ], $addrInfo)]);
            }

            //通过1688接口获取到运费
            if($expressPrice <= 0){
                $app = AlibabaApp::findOne($goods->app_id);
                $distribution = new Distribution($app->app_key, $app->secret);

                //解析1688的地址
                $res = $distribution->requestWithToken(new GetAddress([
                    "addressInfo" => "{$userAddress->province} {$userAddress->city} {$userAddress->district}{$userAddress->detail}"
                ]), $app->access_token);
                if(empty($res->error)){
                    //1688预览订单接口获取运费信息
                    $aliPreviewData = AlibabaDistributionOrderForm::getAliOrderPreviewData($distribution, $app->access_token, [
                        'offerId'   => $goods->ali_offerId,
                        'specId'    => $spec,
                        'quantity'  => $num
                    ], $userAddress,  $res->result);

                    if(is_array($aliPreviewData) && count($aliPreviewData) > 0){
                        foreach($aliPreviewData as $previewData){
                            $expressPrice += floatval($previewData['sumCarriage']/100);
                        }
                    }
                }
            }

            $number += $expressPrice * (1/AlibabaDistributionOrderForm::getShoppingVoucherDecodeExpressRate());
        }

        return $number;
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $goods = AlibabaDistributionGoodsList::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }
            $aliInfo = (array)@json_decode($goods->ali_product_info, true);

            $detail['id']               = $goods->id;
            $detail['name']             = $goods->name;
            $detail['shopping_voucher'] = static::getShoppingVoucherDecodeNeedNumber($goods, 0, '', $goods->price, 1);
            $detail['price']            = $goods->price;
            $detail['origin_price']     = $goods->origin_price;
            if(isset($aliInfo['info'])){
                $detail['images']           = $aliInfo['info']['image']['images'];
                $detail['saleInfo']         = $aliInfo['info']['saleInfo'];
                $detail['categoryName']     = $aliInfo['info']['categoryName'];
                $detail['mainVedio']        = isset($aliInfo['info']['mainVedio']) ? $aliInfo['info']['mainVedio'] : '';
                $detail['shippingInfo']     = $aliInfo['info']['shippingInfo'];
                $detail['description']      = $aliInfo['info']['description'];

                $attributeGroup = [];
                foreach($aliInfo['info']['attributes'] as $attr){
                    if(!isset($attributeGroup[$attr['attributeID']])){
                        $attributeGroup[$attr['attributeID']] = [
                            "attributeID"   => $attr["attributeID"],
                            "attributeName" => $attr['attributeName'],
                            "isCustom"      => $attr['isCustom'],
                            "values"        => []
                        ];
                    }
                    $attributeGroup[$attr['attributeID']]['values'][] = $attr['value'];
                }
                $attributes = [];
                foreach ($attributeGroup as $attr){
                    $attributes[] = $attr;
                }

                $detail['attributes'] = $aliInfo['info']['attributes'];
                $detail['attributes'] = $attributes;

            }else{
                $detail['images']           = [];
                $detail['saleInfo']         = [];
                $detail['categoryName']     = "";
                $detail['mainVedio']        = "";
                $detail['shippingInfo']     = [];
                $detail['description']      = "";
                $detail['attributes']       = [];
            }

            //规格
            $selects = ["id", "goods_id", "ali_sku_id", "ali_attributes", "ali_spec_id", "price", "origin_price"];
            $skuList = AlibabaDistributionGoodsSku::find()->where([
                "goods_id"  => $goods->id,
                "is_delete" => 0
            ])->select($selects)->asArray()->all();
            $detail['sku_infos'] = @json_decode($goods->sku_infos, true);
            $detail['sku_list'] = [];
            if(!empty($detail['sku_infos']['group'])){
                $skuValues = isset($detail['sku_infos']['values']) ? $detail['sku_infos']['values'] : [];
                if($skuList){
                    foreach($skuList as &$skuItem){
                        $attributes = explode(",", $skuItem['ali_attributes']);
                        $skuItem['labels'] = [];
                        foreach($attributes as $value){
                            if(isset($skuValues[$value])){
                                $skuItem['labels'][] = $skuValues[$value];
                            }
                        }
                        $skuItem['labels'] = implode(",", $skuItem['labels']);
                        //unset($skuItem['ali_attributes']);
                    }
                }
                $detail['sku_list'] = is_array($skuList) ? $skuList : [];
            }else{
                $detail['sku_infos'] = [];
            }

            //无规格商品处理
            if(empty($detail['sku_infos']['group'])){
                $detail['sku_infos'] = static::getDefaultSkuInfos($goods);
                $detail['sku_list'] = static::getDefaultSkuList($goods);
            }

            //计算各个规格使用购物券兑换的价格
            foreach($detail['sku_list'] as &$skuItem){
                $skuItem['shopping_voucher'] = static::getShoppingVoucherDecodeNeedNumber($goods,
                    $skuItem['id'] == "DEF" ? 0 : $skuItem['id'], $skuItem['ali_spec_id'], $skuItem['price'], 1);
            }

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'detail' => $detail
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 获取默认规格组
     * @params AlibabaDistributionGoodsList $goods
     * @return array
     */
    public static function getDefaultSkuInfos(AlibabaDistributionGoodsList $goods){
        $skuInfos['group']['DEF'] = ['attributeID' => 'DEF', 'skuImageUrl' => '', 'attributeName' => '默认'];
        $skuInfos['values']['DEF:0'] = '默认';
        return $skuInfos;
    }

    /**
     * 获取默认规格列表
     * @params AlibabaDistributionGoodsList $goods
     * @return array
     */
    public static function getDefaultSkuList(AlibabaDistributionGoodsList $goods){
        $item = [
            'id' => 'DEF',
            'goods_id' => $goods->id,
            'ali_sku_id' => 'DEF',
            'ali_attributes' => 'DEF:0',
            'ali_spec_id' => '-1',
            'price' => $goods->price,
            'origin_price' => $goods->origin_price,
            'labels' => '默认'
        ];
        return [$item];
    }


    /**
     * @return array
     */
    public function getCacheKey(){
        return [$this->id, $this->user_id, $this->mall_id];
    }
}