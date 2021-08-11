<?php

namespace app\forms\api\clerkCenter;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\clerk\ClerkData;

class ClerkDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $clerkData = ClerkData::findOne($this->id);
            if(!$clerkData){
                throw new \Exception("核销数据不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'clerk_status' => $clerkData->status == 1 ? 1 : 0
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }

    }

}