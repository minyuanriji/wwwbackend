<?php

namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\mch\models\MchSubAccount;

class MchSubAccountDeleteForm extends BaseModel{

    public $mch_id;
    public $user_id;

    public function rules(){
        return [
            [['mch_id', 'user_id'], 'required']
        ];
    }

    public function delete(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $subAccount = MchSubAccount::findOne([
                "mch_id"  => $this->mch_id,
                "user_id" => $this->user_id
            ]);
            if($subAccount){
                $subAccount->delete();
            }

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