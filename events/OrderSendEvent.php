<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单发送事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use yii\base\Event;

class OrderSendEvent extends Event
{
    public $order;
}
