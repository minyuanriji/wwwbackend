<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class FromGoodsDeleteForm extends BaseModel{

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

            $fromGoods = ShoppingVoucherFromGoods::findOne($this->id);
            if(!$fromGoods){
                throw new \Exception("数据不存在");
            }

            $fromGoods->is_delete = 1;
            $fromGoods->deleted_at = time();
            if(!$fromGoods->save()){
                throw new \Exception($this->responseErrorMsg($fromGoods));
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