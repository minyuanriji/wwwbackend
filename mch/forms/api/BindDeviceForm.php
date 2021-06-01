<?php
namespace app\mch\forms\api;


use app\core\ApiCode;
use app\forms\api\identity\SmsForm;
use app\helpers\sms\Sms;
use app\models\Admin;
use app\models\BaseModel;
use app\models\User;
use app\plugins\mch\models\Mch;

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
            /*$smsForm = new SmsForm();
            $smsForm->mobile = $this->mobile;
            $smsForm->captcha = $this->verify_code;
            if (!$smsForm->checkCode()) {
                throw new \Exception("验证码不正确");
            }*/

            $mch = Mch::findOne([
                'mobile'    => $this->mobile,
                'is_delete' => 0
            ]);
            if(!$mch){
                throw new \Exception("无法获取到商户信息");
            }

            $security = \Yii::$app->getSecurity();

            $admin = Admin::findOne(["mch_id" => $mch->id]);
            if(!$admin){
                $admin = new Admin([
                    'username'     => "A:" . uniqid(),
                    'password'     => $security->generatePasswordHash(uniqid()),
                    'mall_id'      => 5,
                    'mch_id'       => $mch->id,
                    'admin_type'   => '3',
                    'mall_num'     => 0,
                    'expired_at'   => 0,
                    'is_delete'    => 0,
                    'created_at'   => time(),
                    'updated_at'   => time()
                ]);

            }

            $admin->auth_key = \Yii::$app->security->generateRandomString();
            $admin->access_token = \Yii::$app->security->generateRandomString();

            if(!$admin->save()){
                throw new \Exception("账号保存失败");
            }

            $user = User::findOne($mch->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取到商户所属小程序账号");
            }

            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->access_token = \Yii::$app->security->generateRandomString();
            if(!$user->save()){
                throw new \Exception("小程序账号信息保存失败");
            }

            //Sms::updateCodeStatus($this->mobile, $this->verify_code);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '登陆成功',
                'data' => [
                    'auth_key'     => $user->auth_key,
                    'mobile'       => $this->mobile,
                    'access_token' => $user->access_token
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