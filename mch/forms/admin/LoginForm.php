<?php
namespace app\mch\forms\admin;

use app\mch\models\MchAdmin;
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

            $user = User::findOne(['username' => $this->username, 'is_delete' => 0]);
            if(!$user){
                throw new \Exception('账号不存在');
            }

            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $user->password)) {
                throw new \Exception('密码错误:' . $this->password);
            }

            $mchModel = $user->mch_id ? Mch::findOne($user->mch_id) : null;

            if(!$mchModel || $mchModel->is_delete){
                throw new \Exception('商户不存在');
            }

            if($mchModel->review_status != Mch::REVIEW_STATUS_CHECKED){
                throw new \Exception('商户正在审核中:' . $mchModel->id);
            }

            $adminModel = MchAdmin::findOne(['mch_id' => $mchModel->id, 'is_delete' => 0]);
            if (!$adminModel) {
                $adminModel = new MchAdmin();
                $adminModel->username   = $user->username;
                $adminModel->password   = $user->password;
                $adminModel->mall_id    = $user->mall_id;
                $adminModel->mch_id     = $mchModel->id;
                $adminModel->admin_type = MchAdmin::ADMIN_TYPE_OPERATE;
                if(!$adminModel->save()){
                    throw new \Exception('系统异常！');
                }
            }

            $adminModel->mchModel  = $mchModel;

            $duration = $this->checked == 'true' ? 86400 : 0;
            $res = \Yii::$app->user->login($adminModel, $duration);

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
