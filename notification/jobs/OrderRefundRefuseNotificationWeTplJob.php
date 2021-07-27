<?php
namespace app\notification\jobs;


use app\notification\OrderRefundRefuseNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderRefundRefuseNotificationWeTplJob extends Component implements JobInterface
{
    public $order_refund;

    public function execute($queue)
    {
        OrderRefundRefuseNotification::sendWechatTemplate($this->order_refund);
    }
}