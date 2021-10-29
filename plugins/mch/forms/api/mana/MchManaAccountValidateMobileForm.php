<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAdminUser;

class MchManaAccountValidateMobileForm extends BaseModel{

    public $mobile;
    public $captcha;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mobile', 'captcha'], 'required']
        ]);
    }

    public function check(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            $mch = Mch::findOne([
                "mobile"        => $this->mobile,
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            $adminUser = MchAdminUser::findOne(["mch_id" => $mch->id]);
            $adminUser->auth_key        = \Yii::$app->getSecurity()->generateRandomString();
            $adminUser->auth_expired_at = time() + 15 * 60;
            if(!$adminUser->save()){
                throw new \Exception($this->responseErrorMsg($adminUser));
            }

            Sms::updateCodeStatus($this->mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "验证通过",
                "data" => [
                    "auth_key" => $adminUser->auth_key
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}