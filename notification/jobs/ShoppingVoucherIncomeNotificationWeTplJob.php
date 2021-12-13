<?php

namespace app\notification\jobs;

use app\notification\ShoppingVoucherIncomeNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class ShoppingVoucherIncomeNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        ShoppingVoucherIncomeNotification::sendWechatTemplate($this->voucher_log);
    }
}