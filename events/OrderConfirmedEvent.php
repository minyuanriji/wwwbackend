<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单确认事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use yii\base\Event;

class OrderConfirmedEvent extends Event
{
    public $order;
}
