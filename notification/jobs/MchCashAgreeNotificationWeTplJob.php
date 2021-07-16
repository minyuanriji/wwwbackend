<?php

namespace app\notification\jobs;

use app\notification\MchCashAgreeNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchCashAgreeNotificationWeTplJob extends Component implements JobInterface
{
    public $mch_cash;

    public function execute($queue)
    {
        MchCashAgreeNotification::sendWechatTemplate($this->mch_cash);
    }
}