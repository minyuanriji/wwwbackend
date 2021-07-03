<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 9:52
 */

namespace app\events;


use yii\base\Event;

class UserInfoEvent  extends Event
{

    public $user_id;
    public $mall_id;
    public $is_inviter;
    public $parent_id;
    public $before_parent_id;

}
