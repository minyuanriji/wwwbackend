<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherTargetAlibabaDistributionGoods;

class ShoppingVoucherAlibabaDistributionGoodsDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $voucherGoods = ShoppingVoucherTargetAlibabaDistributionGoods::findOne($this->id);
            if(!$voucherGoods){
                throw new \Exception("红包商品不存在");
            }

            $voucherGoods->is_delete = 1;
            $voucherGoods->deleted_at = time();
            if(!$voucherGoods->save()){
                throw new \Exception($this->responseErrorMsg($voucherGoods));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}