<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;

class BindDeviceForm extends BaseModel{

    public $mobile;
    public $verify_code;

    public function rules(){
        return [
            [['mobile', 'verify_code'], 'required']
        ];
    }

    public function bind(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            throw new \Exception("商户不存在");
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }


    }

}