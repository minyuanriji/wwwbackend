<?php

namespace app\notification\jobs;

use app\notification\MchCashRefuseNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchCashRefuseNotificationWeTplJob extends Component implements JobInterface
{
    public $mch_cash;

    public function execute($queue)
    {
        MchCashRefuseNotification::sendWechatTemplate($this->mch_cash);
    }
}