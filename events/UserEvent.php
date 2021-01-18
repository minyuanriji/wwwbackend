<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用户事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use app\models\User;
use yii\base\Event;

class UserEvent extends Event
{
    /** @var User $user */
    public $user;
    public $user_id;
}
