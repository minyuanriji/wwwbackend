<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;

class ClerkDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {



        }catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}