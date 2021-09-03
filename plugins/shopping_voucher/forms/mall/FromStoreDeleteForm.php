<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\Shopping_voucher\models\ShoppingVoucherFromStore;

class FromStoreDeleteForm extends BaseModel{

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

            $fromStore = ShoppingVoucherFromStore::findOne($this->id);
            if(!$fromStore){
                throw new \Exception("数据不存在");
            }

            $fromStore->is_delete = 1;
            $fromStore->deleted_at = time();
            if(!$fromStore->save()){
                throw new \Exception($this->responseErrorMsg($fromStore));
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