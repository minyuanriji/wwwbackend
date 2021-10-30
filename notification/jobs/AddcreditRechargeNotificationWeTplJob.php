<?php

namespace app\notification\jobs;

use app\notification\AddcreditRechargeNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class AddcreditRechargeNotificationWeTplJob extends Component implements JobInterface
{

    public $voucher_log;

    public function execute($queue)
    {
        AddcreditRechargeNotification::sendWechatTemplate($this->voucher_log);
    }
}