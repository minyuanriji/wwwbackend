<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;

class FromGiftpacksDeleteForm extends BaseModel {

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

            $fromGiftpacks = ShoppingVoucherFromGiftpacks::findOne($this->id);
            if(!$fromGiftpacks){
                throw new \Exception("数据不存在");
            }

            $fromGiftpacks->is_delete = 1;
            $fromGiftpacks->deleted_at = time();
            if(!$fromGiftpacks->save()){
                throw new \Exception($this->responseErrorMsg($fromGiftpacks));
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