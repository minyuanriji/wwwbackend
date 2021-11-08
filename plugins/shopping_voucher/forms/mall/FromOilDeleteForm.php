<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromOil;

class FromOilDeleteForm extends BaseModel {

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

            $fromOil = ShoppingVoucherFromOil::findOne($this->id);
            if(!$fromOil){
                throw new \Exception("数据不存在");
            }

            $fromOil->is_delete  = 1;
            $fromOil->updated_at = time();
            if(!$fromOil->save()){
                throw new \Exception($this->responseErrorMsg($fromOil));
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