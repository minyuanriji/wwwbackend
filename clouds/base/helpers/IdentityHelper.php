<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 17:42
 */
namespace app\clouds\base\helpers;

use app\clouds\base\user\Identity;
use yii\base\BaseObject;

class IdentityHelper extends BaseObject
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
        return !\Yii::$app->cloudUser->isGuest ? \Yii::$app->cloudUser->getIdentity() : null;
    }

    /**
     * 客户端登陆
     * @param Identity $cloudUser
     */
    public static function login(Identity $identity)
    {
        \Yii::$app->cloudUser->login($identity);
    }
}