<?php

namespace app\notification\jobs;

use app\notification\ReservationSuccessNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class ReservationSuccessNotificationWeTplJob extends Component implements JobInterface
{

    public $hotel_order;

    public function execute($queue)
    {
        ReservationSuccessNotification::sendWechatTemplate($this->hotel_order);
    }
}