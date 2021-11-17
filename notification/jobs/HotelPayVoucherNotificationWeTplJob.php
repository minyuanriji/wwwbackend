<?php

namespace app\notification\jobs;

use app\notification\HotelPayVoucherNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class HotelPayVoucherNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        HotelPayVoucherNotification::sendWechatTemplate($this->voucher_log);
    }
}