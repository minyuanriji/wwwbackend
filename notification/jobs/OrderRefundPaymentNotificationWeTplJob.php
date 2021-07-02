<?php

namespace app\notification\jobs;

use app\notification\OrderRefundPaymentNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderRefundPaymentNotificationWeTplJob extends Component implements JobInterface
{
    public $order_refund;

    public function execute($queue)
    {
        OrderRefundPaymentNotification::sendWechatTemplate($this->order_refund);
    }
}