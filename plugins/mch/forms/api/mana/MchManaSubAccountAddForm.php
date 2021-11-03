<?php

namespace app\plugins\mch\forms\api\mana;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\MchAdminUser;

class MchManaSubAccountAddForm extends BaseModel{

    public $mobile;
    public $captcha;

    public function rules(){
        return [
            [['mobile', 'captcha'], 'required'],
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->mobile;
            if(!$smsForm->checkCode()){
                throw new \Exception("手机验证码不正确");
            }

            $subAdminUser = MchAdminUser::findOne([
                "mobile" => $this->mobile,
                "is_sub" => 1
            ]);
            if($subAdminUser){
                throw new \Exception("该手机已是另一个商户的子账号，请先解绑后再进行绑定操作");
            }

            $subAdminUser = new MchAdminUser([
                "mall_id"    => MchAdminController::$adminUser['mall_id'],
                "mch_id"     => MchAdminController::$adminUser['mch_id'],
                "mobile"     => $this->mobile,
                "created_at" => time(),
                "is_sub"     => 1
            ]);
            if(!$subAdminUser->save()){
                throw new \Exception(json_encode($subAdminUser->getErrors()));
            }

            Sms::updateCodeStatus($this->mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}