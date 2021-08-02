<?php
namespace app\notification\jobs;


use app\notification\OrderRefundSuccessNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderRefundSuccessNotificationWeTplJob extends Component implements JobInterface
{
    public $order_refund;

    public function execute($queue)
    {
        OrderRefundSuccessNotification::sendWechatTemplate($this->order_refund);
    }
}