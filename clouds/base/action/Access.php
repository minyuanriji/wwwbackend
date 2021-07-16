<?php
/**
 * @link:http://www.######.com/
 * @copyright: Copyright (c) #### ########
 * 操作访问凭证
 * Author: Mr.Lin
 * Email: 746027209@qq.com
 * Date: 2021-07-07 17:20
 */
namespace app\clouds\base\action;

use app\clouds\base\helpers\TimeHelper;
use app\clouds\base\tables\CloudUser;
use app\clouds\base\user\User;
use yii\base\BaseObject;

class Access extends BaseObject
{
    private $expired_at; //过期时间

    /**
     * 是否已过期
     * @return bool
     */
    public function isExpired()
    {
        return TimeHelper::timestamp() < $this->expired_at;
    }

    final public static function get(Action $action)
    {
        User::login(CloudUser::findOne(10009));
        print_r(\Yii::$app->cloudUser);
        exit;
    }
}