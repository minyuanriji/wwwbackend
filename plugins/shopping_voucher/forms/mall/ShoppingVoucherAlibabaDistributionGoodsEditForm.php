<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;
use app\plugins\alibaba\models\AlibabaDistributionGoodsSku;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

class ShoppingVoucherAlibabaDistributionGoodsEditForm extends BaseModel{

    public $id;
    public $goods_id;
    public $sku_id;
    public $name;
    public $cover_pic;
    public $voucher_price;

    public function rules(){
        return [
            [['goods_id', 'sku_id', 'name', 'cover_pic', 'voucher_price'], 'required'],
            [['id', 'goods_id', 'sku_id'], 'integer']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne($this->id);
            if(!$voucherGoods){

                $exists = ShoppingVoucherTargetAlibabaDistributionGoods::findOne([
                    "goods_id" => $this->goods_id,
                    "sku_id"   => $this->sku_id,
                ]);
                if($exists && !$exists->is_delete){
                    throw new \Exception("已添加过该商品了");
                }

                if(!$exists){
                    $voucherGoods = new ShoppingVoucherTargetAlibabaDistributionGoods([
                        "mall_id"    => \Yii::$app->mall->id,
                        "created_at" => time()
                    ]);
                }else{
                    $voucherGoods = $exists;
                    $voucherGoods->is_delete = 0;
                    $voucherGoods->deleted_at = 0;
                }

            }

            $goods = AlibabaDistributionGoodsList::findOne($this->goods_id);
            if(!$goods || $goods->is_delete){
                throw new \Exception("商品不存在");
            }

            $sku = AlibabaDistributionGoodsSku::findOne($this->sku_id);
            if(!$sku || $sku->is_delete){
                throw new \Exception("规格不存在");
            }

           /* $skuInfos = @json_decode($goods->sku_infos, true);
            $attrs = explode(",", $sku->ali_attributes);
            $labels = [];
            foreach($attrs as $attr){
                if(!empty($attr) && isset($skuInfos['values'][$attr])){
                    $part = explode(":", $attr);
                    $attrId = isset($part[0]) ? $part[0] : 0;
                    if(isset($skuInfos['group'][$attrId])){
                        $labels[] = $skuInfos['group'][$attrId]['attributeName'] . "：" . $skuInfos['values'][$attr];
                    }
                }
            }*/

            $voucherGoods->sku_id        = $this->sku_id;
            $voucherGoods->goods_id      = $this->goods_id;
            $voucherGoods->name          = $this->name;
            $voucherGoods->cover_pic     = $this->cover_pic;
            $voucherGoods->voucher_price = $this->voucher_price;
            $voucherGoods->updated_at    = time();

            if(!$voucherGoods->save()){
                throw new \Exception($this->responseErrorMsg($voucherGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}