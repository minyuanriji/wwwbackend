<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
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

            //手机验证码验证
            $smsForm = new SmsForm();
            $smsForm->mobile = $this->mobile;
            $smsForm->captcha = $this->verify_code;
            if (!$smsForm->checkCode()) {
                throw new \Exception("验证码不正确");
            }

            throw new \Exception("商户不存在");

            Sms::updateCodeStatus($this->mobile, $this->verify_code);

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }


    }

}