<?php
namespace app\mch\forms\admin;

use app\mch\models\MchAdmin;
use app\models\Admin;
use app\models\BaseModel;
use app\core\ApiCode;
use app\models\User;
use app\plugins\mch\models\Mch;
use Yii;

class LoginForm extends BaseModel{
    public $username;
    public $password;
    public $captcha;
    public $checked;

    public function rules(){
        $rules = [
            [['username', 'password', 'captcha', 'checked'], 'required'],
            [['captcha'], 'captcha', 'captchaAction' => 'site/captcha']
        ];

        if (YII_ENV == 'dev') {
            $rules = [
                [['username', 'password', 'captcha', 'checked'], 'required'],
            ];
        }

        return $rules;
    }

    public function attributeLabels(){
        return [
            'username' => '用户名',
            'password' => '密码',
            'mall_id' => '商城ID',
            'captcha' => '验证码',
        ];
    }

    public function login(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {

            $adminModel = MchAdmin::findOne(['username' => $this->username, 'is_delete' => 0]);
            if(!$adminModel){
                throw new \Exception('商户账号不存在');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $adminModel->password)) {
                throw new \Exception('密码错误:' . $this->password);
            }

            $mchModel = $adminModel->mch_id ? Mch::findOne($adminModel->mch_id) : null;

            if(!$mchModel || $mchModel->is_delete){
                throw new \Exception('商户不存在');
            }

            if($mchModel->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception('商户正在审核中:' . $mchModel->id);
            }

            $adminModel->mchModel  = $mchModel;

            $duration = $this->checked == 'true' ? 86400 : 0;
            $res = \Yii::$app->mchAdmin->login($adminModel, $duration);

            setcookie('__mch_login_route', '/mch/admin/login');
            $route = 'mch/overview/index';

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '登录成功',
                'data' => [
                    'url' => $route
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage(),
                'error' => [
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}
