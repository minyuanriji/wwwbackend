<?php
namespace app\notification\jobs;


use app\notification\CashPaidNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class CashPaidNotificationWeTplJob extends Component implements JobInterface
{
    public $cash;

    public function execute($queue)
    {
        CashPaidNotification::sendWechatTemplate($this->cash);
    }
}