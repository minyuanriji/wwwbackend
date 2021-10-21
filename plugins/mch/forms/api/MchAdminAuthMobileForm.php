<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAdminUser;

class MchAdminAuthMobileForm extends BaseModel{

    public $mobile;
    public $verify_code;

    public function rules(){
        return [
            [['mobile', 'verify_code'], 'required']
        ];
    }

    public function login(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //手机验证码验证
            $smsForm = new SmsForm();
            $smsForm->mobile  = $this->mobile;
            $smsForm->captcha = $this->verify_code;
            if (!$smsForm->checkCode()) {
                throw new \Exception("验证码不正确");
            }

            $mch = Mch::findOne([
                'mobile'        => $this->mobile,
                'review_status' => Mch::REVIEW_STATUS_CHECKED,
                'is_delete'     => 0
            ]);
            if(!$mch){
                throw new \Exception("商户不存在");
            }

            $adminUser = MchAdminUser::findOne([
                "mall_id" => $mch->mall_id,
                "mch_id"  => $mch->id
            ]);
            if(!$adminUser){
                $adminUser = new MchAdminUser([
                    "mall_id"    => $mch->mall_id,
                    "mch_id"     => $mch->id,
                    "created_at" => time()
                ]);
            }
            $adminUser->last_login_at = time();
            $adminUser->login_ip      = \Yii::$app->getRequest()->getUserIP();
            $adminUser->auth_key      = \Yii::$app->security->generateRandomString();
            $adminUser->access_token  = \Yii::$app->security->generateRandomString();
            if(!$adminUser->save()){
                throw new \Exception(json_encode($adminUser->getErrors()));
            }

            Sms::updateCodeStatus($this->mobile, $this->verify_code);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '登陆成功',
                'data' => [
                    'auth_key'     => $adminUser->auth_key,
                    'mobile'       => $this->mobile,
                    'access_token' => $adminUser->access_token
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