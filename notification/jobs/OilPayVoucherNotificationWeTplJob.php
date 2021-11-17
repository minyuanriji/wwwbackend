<?php

namespace app\notification\jobs;

use app\notification\OilPayVoucherNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class OilPayVoucherNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        OilPayVoucherNotification::sendWechatTemplate($this->voucher_log);
    }
}