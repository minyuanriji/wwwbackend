<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单退款事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use app\models\OrderRefund;
use yii\base\Event;

/**
 * @property OrderRefund $order_refund
 */
class OrderRefundEvent extends Event
{
    public $order_refund;
    public $advance_refund;
}
