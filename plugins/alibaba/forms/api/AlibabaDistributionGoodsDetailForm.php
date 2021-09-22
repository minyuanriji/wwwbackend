<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;

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
            $detail['shopping_voucher'] = $goods->price; //TODO 购物券价
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
            if(empty($detail['sku_infos'])){
                $detail['sku_infos'] = static::getDefaultSkuInfos($goods);
                $detail['sku_list'] = static::getDefaultSkuList($goods);
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