<?php
namespace app\mch\forms\api\mch_bind_mobile;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;

class UpdateMobileForm extends BaseModel{

    public $mch_id;
    public $new_mobile;
    public $captcha;
    public $auth_key;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id', 'captcha', 'new_mobile', 'auth_key'], 'required']
        ]);
    }

    public function save(){
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

            $store = Store::findOne(["mch_id" => $mch->id]);
            if(!$store){
                throw new \Exception("无法获取门店信息");
            }

            $user = User::findOne($mch->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("商户未绑定小程序号");
            }

            if($user->auth_key != $this->auth_key){
                throw new \Exception("授权码不正确");
            }

            if($user->auth_expire_dt < time()){
                throw new \Exception("授权码已过期，请重新验证手机");
            }

            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile  = $this->new_mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            $exists = Mch::find()->andWhere([
                "AND",
                "id <> '".$mch->id."'",
                ["mobile" => $this->new_mobile]
            ])->exists();
            if($exists){
                throw new \Exception("手机号“".$this->new_mobile."“已存在");
            }

            $mch->mobile = $this->new_mobile;
            if(!$mch->save()){
                throw new \Exception($this->responseErrorMsg($mch));
            }

            $store->mobile = $this->new_mobile;
            if(!$store->save()){
                throw new \Exception($this->responseErrorMsg($store));
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