<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use app\models\Order;
use yii\base\Event;

class OrderEvent extends Event
{
    /** @var Order */
    public $order;
    public $cartIds = [];
    public $pluginData;
    public $order_type;
    public $formData;
    public $orderItem;
}
