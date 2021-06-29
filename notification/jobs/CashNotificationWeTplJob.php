<?php
namespace app\notification\jobs;


use app\notification\CashNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class CashNotificationWeTplJob extends Component implements JobInterface
{
    public $cash;

    public function execute($queue)
    {
        CashNotification::sendWechatTemplate($this->cash);
    }
}