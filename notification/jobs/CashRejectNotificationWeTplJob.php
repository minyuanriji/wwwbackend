<?php
namespace app\notification\jobs;


use app\notification\CashRejectNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class CashRejectNotificationWeTplJob extends Component implements JobInterface
{
    public $cash;

    public function execute($queue)
    {
        CashRejectNotification::sendWechatTemplate($this->cash);
    }
}