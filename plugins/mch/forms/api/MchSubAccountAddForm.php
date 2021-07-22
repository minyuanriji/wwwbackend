<?php

namespace app\plugins\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchSubAccount;

class MchSubAccountAddForm extends BaseModel{

    public $mch_id;
    public $mobile;
    public $captcha;

    public function rules(){
        return [
            [['mch_id', 'mobile', 'captcha'], 'required'],
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

            $user = User::findOne(["mobile" => $this->mobile]);
            if(!$user || $user->is_delete){
                throw new \Exception("子账户用户信息不存在");
            }

            $mch = Mch::findOne($this->mch_id);
            if(!$mch || $mch->is_delete){
                throw new \Exception("商户不存在");
            }

            if($mch->user_id == $user->id){
                throw new \Exception("主账户不能作为子账户");
            }

            $subAccount = MchSubAccount::findOne(["user_id" => $user->id]);
            if(!$subAccount){
                $subAccount = new MchSubAccount([
                    'mall_id' => $user->mall_id,
                    'user_id' => $user->id,
                    'mch_id'  => $this->mch_id,
                    'created_at' => time()
                ]);
            }

            $subAccount->updated_at = time();
            if(!$subAccount->save()){
                throw new \Exception($this->responseErrorMsg($subAccount));
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