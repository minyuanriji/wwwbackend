<?php

namespace app\notification\jobs;

use app\notification\GoodsPayVoucherNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class GoodsPayVoucherNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        GoodsPayVoucherNotification::sendWechatTemplate($this->voucher_log);
    }
}