<?php
namespace app\notification\jobs;


use app\notification\OrderPaymentSuccessNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderPaymentSuccessNotificationWeTplJob extends Component implements JobInterface
{

    public $order;

    public function execute($queue)
    {
        OrderPaymentSuccessNotification::sendWechatTemplate($this->order);
    }
}