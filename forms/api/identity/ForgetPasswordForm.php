<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * api忘记密码类
 * Author: zal
 * Date: 2020-04-29
 * Time: 10:16
 */

namespace app\forms\api\identity;

use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\core\ApiCode;
use app\models\User;
use app\validators\PhoneNumberValidator;
use function EasyWeChat\Kernel\Support\get_client_ip;

class ForgetPasswordForm extends BaseModel
{
    public $mobile;
    public $password;
    public $confirm_password;
    public $captcha;

    public function rules()
    {
        return [
            [['mobile', 'password', 'captcha', 'confirm_password'], 'required'],
            [['mobile'], PhoneNumberValidator::className()],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'password' => '密码',
            'captcha' => '验证码',
        ];
    }

    /**
     * 忘记密码
     * @Author: zal
     * @Date: 2020-04-29
     * @Time: 10:13
     * @return array
     * @throws \Exception
     */
    public function forgetPassword()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            if($this->password !== $this->confirm_password){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'两次密码不一致');
            }
            $existUser = User::getOneUser(['or',['=', 'username', $this->mobile],['=', 'mobile', $this->mobile]]);
            if(empty($existUser)){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'手机号不存在');
            }
            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile = $this->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }
            $user = $existUser;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            if (!$user->save()) {
                return $this->responseErrorInfo($user);
            }
            Sms::updateCodeStatus($this->mobile, $this->captcha);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'密码重置成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }

    /**
     * 设置交易密码
     * @Author: zal
     * @Date: 2020-05-07
     * @Time: 15:13
     * @return array
     * @throws \Exception
     */
    public function setTransactionPassword()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            if($this->password !== $this->confirm_password){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'两次密码不一致');
            }
            $user_id = \Yii::$app->user->id;
            /** @var User $user */
            $user = User::getOneData($user_id);
            if($user->mobile != $this->mobile){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'当前操作不是同一个用户');
            }
            if (\Yii::$app->getSecurity()->validatePassword(trim($this->password), $user->password)) {
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'不能与登录密码一致');
            }
            //检测手机验证码是否正确
            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile = $this->mobile;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }
            $user->transaction_password = \Yii::$app->getSecurity()->generatePasswordHash($this->password);
            if (!$user->save()){
                return $this->returnApiResultData(999,"",$user);
            }
            Sms::updateCodeStatus($this->mobile, $this->captcha);
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,'交易密码设置成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,CommonLogic::getExceptionMessage($e));
        }
    }
}
