<?php

namespace app\plugins\mch\forms\api\mana;

use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\plugins\mch\controllers\api\mana\MchAdminController;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchAdminUser;

class MchManaAccountUpdateMobileForm extends BaseModel {

    public $new_mobile;
    public $captcha;
    public $auth_key;

    public function rules(){
        return array_merge(parent::rules(), [
            [['captcha', 'new_mobile', 'auth_key'], 'required']
        ]);
    }

    public function update(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->new_mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            $mch = Mch::findOne([
                "id"            => MchAdminController::$adminUser['mch_id'],
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            $adminUser = MchAdminUser::findOne(["mch_id" => $mch->id]);
            if($adminUser->auth_key != $this->auth_key){
                throw new \Exception("授权码不正确");
            }

            if($adminUser->auth_expired_at < time()){
                throw new \Exception("授权码已过期，请重新验证手机");
            }

            $exists = Mch::find()->andWhere([
                "AND",
                "id <> '".$mch->id."'",
                ["mobile" => $this->new_mobile]
            ])->exists();
            if($exists){
                throw new \Exception("手机号“".$this->new_mobile."“已被绑定");
            }

            $mch->mobile = $this->new_mobile;
            if(!$mch->save()){
                throw new \Exception($this->responseErrorMsg($mch));
            }

            Sms::updateCodeStatus($this->new_mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功"
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}