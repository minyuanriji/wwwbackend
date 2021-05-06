<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录表单类
 * Author: zal
 * Date: 2020-04-08
 * Time: 15:16
 */

namespace app\forms\admin;

use app\component\jobs\AdminActionJob;
use app\models\ActionLog;
use app\models\Admin;
use app\models\BaseModel;
use app\models\Mall;
use app\core\ApiCode;

class LoginForm extends BaseModel
{
    public $username;
    public $password;
    public $mall_id;
    public $captcha;
    public $checked;

    public function rules()
    {
        $rules = [
            [['username', 'password', 'captcha', 'checked'], 'required'],
            [['mall_id'], 'string'],
            [['captcha'], 'captcha', 'captchaAction' => 'site/captcha','message'=>'{attribute}有误']
        ];

        /*if (YII_ENV == 'dev') {
            $rules = [
                [['username', 'password', 'captcha', 'checked'], 'required'],
                [['mall_id'], 'string'],
            ];
        }*/

        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'mall_id' => '商城ID',
            'captcha' => '验证码',
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        $mallId = base64_decode($this->mall_id);
        try {
            $admin = Admin::findOne(['username' => $this->username, 'is_delete' => 0]);
            if (!$admin) {
                throw new \Exception('账号不存在');
            }
            if (!\Yii::$app->getSecurity()->validatePassword($this->password, $admin->password)) {
                throw new \Exception('密码错误');
            }
            $adminType = (int)$admin->admin_type;
            if ($adminType === Admin::ADMIN_TYPE_OPERATE && !empty($mallId)) {
                //如果是操作员登录，商城id不匹配，则提示错误信息
                if ($mallId != $admin->mall_id) {
                    throw new \Exception('账号不存在');
                }
                $mall = Mall::findOne($mallId);
                if (!$mall) {
                    throw new \Exception('商城不存在，ID:' . $mallId);
                }
                if ($mall->expired_at != 0 && $mall->expired_at < time()) {
                    throw new \Exception('商城已过期');
                }
            }

            if ($adminType === Admin::ADMIN_TYPE_ADMIN && $admin->expired_at !== 0 && time() > strtotime($admin->expired_at)) {
                throw new \Exception('账户已过期！请联系管理员');
            }
            $duration = $this->checked == 'true' ? 86400 : 0;
            $res = \Yii::$app->admin->login($admin, $duration);
            setcookie('__admin_login_route', '/admin/admin/login');
            if ($adminType == Admin::ADMIN_TYPE_SUPER || $adminType == Admin::ADMIN_TYPE_ADMIN) {
                // 管理员
                $route = 'admin/index/index';
                setcookie('__admin_login_role', 'admin');
            } else {
                // 操作员
                $route = 'mall/overview/index';
                \Yii::$app->setSessionJxMallId($this->mall_id);
                setcookie('__admin_login_role', 'staff');
                setcookie('__mall_id', $this->mall_id);
            }
            $dataArr = [
                'newBeforeUpdate' => [],
                'newAfterUpdate' => [],
                'modelName' => 'app\models\Admin',
                'modelId' => $admin->id,
                'remark' => $adminType == Admin::ADMIN_TYPE_ADMIN || $adminType == Admin::ADMIN_TYPE_SUPER ? '管理员登录' : '操作员登录',
                'operator' => $admin->id,
                'mall_id' => $mallId,
                'from' => ActionLog::FROM_AFTER
            ];
            $class = new AdminActionJob($dataArr);
            $queueId = \Yii::$app->queue->delay(0)->push($class);
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
