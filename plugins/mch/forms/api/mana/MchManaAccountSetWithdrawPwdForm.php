<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;

class MchManaAccountSetWithdrawPwdForm extends BaseModel {

    public $withdraw_pwd;
    public $mobile;
    public $captcha;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mobile', 'withdraw_pwd', 'captcha'], 'required'],
            [['withdraw_pwd'], 'string', 'min' => 6, 'max' => 6]
        ]);
    }
    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $mch = Mch::findOne([
                "id" => MchAdminController::$adminUser['mch_id'],
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            if(!empty($mch->mobile) && $mch->mobile != $this->mobile){
                throw new \Exception("商户绑定的手机号码不正确");
            }

            $mch->mobile       = $this->mobile;
            $mch->withdraw_pwd = \Yii::$app->getSecurity()->generatePasswordHash($this->withdraw_pwd);

            if(!$mch->save()){
                throw new \Exception($this->responseErrorMsg($mch));
            }

            Sms::updateCodeStatus($mch->mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
            ];
        }
    }

}