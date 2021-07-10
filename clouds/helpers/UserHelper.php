<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 17:42
 */
namespace app\clouds\helpers;

use app\clouds\tables\CloudUser;
use yii\base\BaseObject;

class UserHelper extends BaseObject
{
    /**
     * 用户是否已登录
     * @return bool
     */
    public static function isLogin()
    {
        return !\Yii::$app->cloudUser->isGuest;
    }

    /**
     * 获取客户端凭证
     * @return Identity|null
     */
    public static function getIdentity()
    {
        return \Yii::$app->cloudUser->getIdentity();
    }

    public static function login(CloudUser $cloudUser)
    {
        \Yii::$app->cloudUser->login($cloudUser);
    }
}