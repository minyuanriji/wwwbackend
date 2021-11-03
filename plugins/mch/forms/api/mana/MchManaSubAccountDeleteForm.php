<?php

namespace app\plugins\mch\forms\api\mana;


use app\core\ApiCode;
use app\models\BaseModel;

class MchManaSubAccountDeleteForm extends BaseModel{

    public $user_id;

    public function rules(){
        return [
            [['user_id'], 'required']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            throw new \Exception("子账号删除功能维护中");

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}