<?php
namespace app\notification\jobs;


use app\notification\HotelRefundSuccessfulNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class HotelRefundSuccessfulNotificationWeTplJob extends Component implements JobInterface
{

    public $hotel_refund_order;

    public function execute($queue)
    {
        HotelRefundSuccessfulNotification::sendWechatTemplate($this->hotel_refund_order);
    }
}