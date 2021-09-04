<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetGoods;

class ShoppingVoucherGoodsEditForm extends BaseModel{

    public $id;
    public $goods_id;
    public $name;
    public $cover_pic;
    public $voucher_price;

    public function rules(){
        return [
            [['goods_id', 'name', 'cover_pic', 'voucher_price'], 'required'],
            [['id', 'goods_id'], 'integer']
        ];
    }

    public function save(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $voucherGoods = ShoppingVoucherTargetGoods::findOne($this->id);
            if(!$voucherGoods){

                $exists = ShoppingVoucherTargetGoods::findOne([
                    "goods_id" => $this->goods_id
                ]);
                if($exists && !$exists->is_delete){
                    throw new \Exception("已添加过该商品了");
                }

                if(!$exists){
                    $voucherGoods = new ShoppingVoucherTargetGoods([
                        "mall_id"    => \Yii::$app->mall->id,
                        "created_at" => time()
                    ]);
                }else{
                    $voucherGoods = $exists;
                    $voucherGoods->is_delete = 0;
                    $voucherGoods->deleted_at = 0;
                }

            }

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