<?php

namespace app\notification\jobs;


use app\notification\MchCashNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchCashNotificationWeTplJob extends Component implements JobInterface
{
    public $mch_cash;

    public function execute($queue)
    {
        MchCashNotification::sendWechatTemplate($this->mch_cash);
    }
}