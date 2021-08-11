<?php
namespace app\notification\jobs;


use app\notification\HotelAfterSalesRejectionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class HotelAfterSalesRejectionNotificationWeTplJob extends Component implements JobInterface
{

    public $hotel_refund_order;

    public function execute($queue)
    {
        HotelAfterSalesRejectionNotification::sendWechatTemplate($this->hotel_refund_order);
    }
}