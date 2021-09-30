<?php

namespace app\plugins\alibaba\forms\api;

use app\controllers\api\ApiController;
use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

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
     * @param float $price 价格
     * @param integer $num 数量
     * @return float
     */
    public static function getShoppingVoucherDecodeNeedNumber(AlibabaDistributionGoodsList $goods, $skuId, $price, $num){
        static $voucherGoodsList = null;
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

        $number = 0; //需要的购物券数量
        if($price > 0 && isset($voucherGoodsList[$goods->id . ":" . $skuId])){
            $voucherPrice = $voucherGoodsList[$goods->id . ":" . $skuId];
            $ratio = $voucherPrice/$price; //购物券价与商品价格比例
            $number = floatval($price) * $ratio * $num;
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
            $expressRate = AlibabaDistributionOrderForm::getShoppingVoucherDecodeExpressRate();

            $goods = AlibabaDistributionGoodsList::findOne($this->id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }
            $aliInfo = (array)@json_decode($goods->ali_product_info, true);

            $detail['id']               = $goods->id;
            $detail['name']             = $goods->name;
            $detail['shopping_voucher'] = $goods->freight_price * (1/$expressRate) + static::getShoppingVoucherDecodeNeedNumber($goods, 0,  $goods->price, 1);
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
            $selects = ["id", "goods_id", "ali_sku_id", "ali_attributes", "ali_spec_id", "name", "price", "origin_price", "freight_price"];
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
                        $skuItem['labels'] = !empty($skuItem['name']) ? $skuItem['name'] : implode(",", $skuItem['labels']);
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
                $skuItem['shopping_voucher'] = $skuItem['freight_price'] * (1/$expressRate) + static::getShoppingVoucherDecodeNeedNumber($goods,
                    $skuItem['id'] == "DEF" ? 0 : $skuItem['id'],  $skuItem['price'], 1);
                $skuItem['shopping_voucher'] = round($skuItem['shopping_voucher'], 2);
            }

            //规格分组
            $detail['sku_group_list'] = $this->skuGroupList($detail['sku_infos'], $detail['sku_list']);

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
     * 规格分组
     * @param $skuInfos
     * @param $skuList
     * @return array
     */
    private function skuGroupList(&$skuInfos, $skuList){
        $skuInfos['group']  = isset($skuInfos['group']) && is_array($skuInfos['group']) ? $skuInfos['group'] : [];
        $skuInfos['values'] = isset($skuInfos['values']) && is_array($skuInfos['values']) ? $skuInfos['values'] : [];

        $attrNames = [];
        foreach($skuInfos['group'] as $attr){
            $attrNames[$attr['attributeID']] = $attr['attributeName'];
        }

        $groupValues = [];
        foreach($skuInfos['values'] as $key => $value){
            $part = explode(":", $key);
            if(!isset($groupValues[$part[0]])){
                $groupValues[$part[0]] = [];
            }
            if(!in_array($key, $groupValues[$part[0]])){
                $groupValues[$part[0]][] = $key;
            }
        }
        $groupValues = array_slice($groupValues, 0, 1);
        //把每个规格加入到各个类目下
        foreach($groupValues as $attrId => $values){
            $arr = [];
            foreach($values as $valueId){
                $item['value_id']   = $valueId;
                $item['value_name'] = $skuInfos['values'][$valueId];
                $item['sku_list']   = [];
                foreach($skuList as $skuItem){
                    if(strpos($skuItem['ali_attributes'], $valueId) === false)
                        continue;
                    $item['sku_list'][] = $skuItem;
                }
                $arr[] = $item;
            }
            $groupValues[$attrId] = $arr;
        }
        //设置下类目下规格名称
        foreach($groupValues as &$groupArr){
            foreach($groupArr as &$item){
                foreach($item['sku_list'] as &$sku){
                    $names = [];
                    foreach(explode(",", $sku['ali_attributes']) as $valueId){
                        if($valueId == $item['value_id']) continue;
                        $names[] = $skuInfos['values'][$valueId];
                    }
                    $sku['name'] = $sku['labels'] = implode("/", $names);
                    $sku['num']  = 0;
                }
            }
        }
        return !empty($groupValues) ? $groupValues[0] : [];
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
            'freight_price' => $goods->freight_price,
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