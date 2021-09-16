<?php

namespace app\notification\jobs;

use app\notification\VoucherConsumptionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class VoucherConsumptionNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        VoucherConsumptionNotification::sendWechatTemplate($this->voucher_log);
    }
}