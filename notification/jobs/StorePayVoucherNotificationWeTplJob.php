<?php

namespace app\notification\jobs;

use app\notification\StorePayVoucherNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class StorePayVoucherNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        StorePayVoucherNotification::sendWechatTemplate($this->voucher_log);
    }
}