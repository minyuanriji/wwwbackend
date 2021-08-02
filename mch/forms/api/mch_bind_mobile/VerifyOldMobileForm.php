<?php
namespace app\mch\forms\api\mch_bind_mobile;

use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\Mch;

class VerifyOldMobileForm extends BaseModel{

    public $mch_id;
    public $captcha;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'captcha'], 'required']
        ]);
    }

    public function verify(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $mch = Mch::findOne([
                "id"            => $this->mch_id,
                "review_status" => Mch::REVIEW_STATUS_CHECKED
            ]);
            if(!$mch || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $mch->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            $user = User::findOne($mch->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("商户未绑定小程序号");
            }

            $user->auth_key       = \Yii::$app->getSecurity()->generateRandomString();
            $user->auth_expire_dt = time() + 15 * 60;
            if(!$user->save()){
                throw new \Exception($this->responseErrorMsg($user));
            }

            Sms::updateCodeStatus($mch->mobile, $this->captcha);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功",
                "data" => [
                    "auth_key" => $user->auth_key
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