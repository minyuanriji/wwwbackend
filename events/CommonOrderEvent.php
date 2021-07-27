<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 9:49
 */

namespace app\events;


use yii\base\Event;

class CommonOrderEvent extends Event
{
    public $common_order_id;
    public $status;
    public $user_id;
    public $mall_id;
    public $order_type=0;
}