<?php

namespace app\notification\jobs;

use app\notification\GiftPayVoucherNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class GiftPayVoucherNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        GiftPayVoucherNotification::sendWechatTemplate($this->voucher_log);
    }
}