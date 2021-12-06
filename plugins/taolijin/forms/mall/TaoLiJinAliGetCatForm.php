<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use lin010\taolijin\Ali;

class TaoLiJinAliGetCatForm extends BaseModel {

    public $ali_type;

    public function rules(){
        return [
            [['ali_type'], 'required']
        ];
    }


    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $acc = AliAccForm::get("ali");

            $ali = new Ali($acc->app_key, $acc->secret_key);
            $res = $ali->cat->getCats();
            if(!empty($res->code)){
                throw new \Exception($res->msg);
            }


            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [

                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }

}