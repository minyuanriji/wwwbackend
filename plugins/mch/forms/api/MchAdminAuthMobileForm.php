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
    public $captcha;

    public function rules(){
        return [
            [['mobile', 'captcha'], 'required']
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
            $smsForm->captcha = $this->captcha;
            if (!$smsForm->checkCode()) {
                throw new \Exception("验证码不正确");
            }

            //子账号登录
            $adminUser = MchAdminUser::find()->where([
                "mobile" => $this->mobile,
                "is_sub" => 1
            ])->one();
            if($adminUser){
                $mch = Mch::findOne($adminUser->mch_id);
            }else{
                $mch = Mch::findOne([
                    'mobile'        => $this->mobile,
                    'review_status' => Mch::REVIEW_STATUS_CHECKED,
                    'is_delete'     => 0
                ]);
            }
            if(!$mch || $mch->is_delete || $mch->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception("商户不存在");
            }

            if(!$adminUser){ //这里是主账号登录
                $adminUser = MchAdminUser::find()->where([
                    "mobile" => $this->mobile,
                    "is_sub" => 0
                ])->one();
                if(!$adminUser){
                    $adminUser = new MchAdminUser([
                        "mall_id"    => $mch->mall_id,
                        "mch_id"     => $mch->id,
                        "mobile"     => $mch->mobile,
                        "created_at" => time()
                    ]);
                } else {
                    if ($adminUser->mch_id != $mch->id) {
                        $adminUser->mch_id = $mch->id;
                    }
                }
            }
            $adminUser->last_login_at    = time();
            $adminUser->token_expired_at = time() + 7 * 24 * 3600;
            $adminUser->login_ip         = \Yii::$app->getRequest()->getUserIP();
            $adminUser->access_token     = \Yii::$app->security->generateRandomString();
            if(!$adminUser->save()){
                throw new \Exception(json_encode($adminUser->getErrors()));
            }

            Sms::updateCodeStatus($this->mobile, $this->captcha);

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