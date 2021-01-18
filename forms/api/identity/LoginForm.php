<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * api登录类
 * Author: zal
 * Date: 2020-04-27
 * Time: 15:16
 */

namespace app\forms\api\identity;

use app\helpers\sms\Sms;
use app\logic\CommonLogic;
use app\models\BaseModel;
use app\core\ApiCode;
use app\models\User;
use function EasyWeChat\Kernel\Support\get_client_ip;

class LoginForm extends BaseModel
{
    public $username;
    public $password;
    public $mall_id;
    public $checked;
    public $captcha;

    public function rules()
    {
        return [
            [['username','captcha'], 'required'],
            [['mall_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'captcha' => '验证码',
            'mall_id' => '商城ID',
        ];
    }

    /**
     * 登录
     * @Author: zal
     * @Date: 2020-04-27
     * @Time: 10:33
     * @return array
     * @throws \Exception
     */
    public function login()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
        try {
            /** @var User $user */
            $user = User::getOneUser(['or',['=', 'username', $this->username],['=', 'mobile', $this->username]]);
            if (empty($user)) {
                throw new \Exception('账号不存在');
            }

//            if (!\Yii::$app->getSecurity()->validatePassword(trim($this->password), $user->password)) {
//                throw new \Exception('密码错误');
//            }
            if(empty($this->captcha)){
                throw new \Exception('验证码不为空');
            }

            $smsForm = new SmsForm();
            $smsForm->captcha = $this->captcha;
            $smsForm->mobile = $this->username;
            if(!$smsForm->checkCode()){
                return $this->returnApiResultData(ApiCode::CODE_FAIL,'验证码不正确');
            }

            $duration = $this->checked == 'true' ? 86400 : 0;
            Sms::updateCodeStatus($this->username, $this->captcha);
            \Yii::$app->user->login($user, $duration);

            $user->last_login_at = time();
            $user->login_ip = get_client_ip();
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->save();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS,"登录成功",['access_token' => $user->access_token]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL,$e->getMessage());
        }
    }
}
