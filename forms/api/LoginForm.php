<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 登录api表单类
 * Author: zal
 * Date: 2020-04-14
 * Time: 14:50
 */

namespace app\forms\api;

use app\events\UserEvent;
use app\models\BaseModel;
use app\models\ErrorLog;
use app\models\User;

abstract class LoginForm extends BaseModel
{
    /**
     * @return LoginUserInfo
     */
    abstract protected function getUserInfo();

    public function authLogin()
    {
        $userInfo = $this->getUserInfo();
        $user = User::find()->where([
            'mall_id' => \Yii::$app->mall->id,
            'username' => $userInfo->username,
            'is_delete' => 0,
        ])->one();
        $t = \Yii::$app->db->beginTransaction();
        $register = false;
        if (!$user) {
            $register = true;
            $user = new User();
            $user->mall_id = \Yii::$app->mall->id;
            $user->access_token = \Yii::$app->security->generateRandomString();
            $user->auth_key = \Yii::$app->security->generateRandomString();
            $user->username = $userInfo->username;
            $user->nickname = $userInfo->nickname;
            $user->password = \Yii::$app->security->generatePasswordHash(\Yii::$app->security->generateRandomString(), 5);
            $user->avatar_url = $userInfo->avatar;
            $user->unionid = $userInfo->platform_user_id;
            $user->platform = $userInfo->platform;
            if (!$user->save()) {
                $t->rollBack();
                return $this->responseErrorInfo($user);
            }
        } else {
            $user->nickname = $userInfo->nickname;
            $user->save();
        }
        $t->commit();

        $event = new UserEvent();
        $event->sender = $this;
        $event->user = $user;
        if ($register) {
            \Yii::$app->trigger(User::EVENT_REGISTER, $event);
        }
        \Yii::$app->trigger(User::EVENT_LOGIN, $event);
        return [
            'code' => 0,
            'data' => [
                'access_token' => $user->access_token,
            ],
        ];
    }
}
