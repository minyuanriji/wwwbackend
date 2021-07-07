<?php
namespace app\notification\jobs;


use app\notification\CashAgreeNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class CashAgreeNotificationWeTplJob extends Component implements JobInterface
{
    public $cash;

    public function execute($queue)
    {
        CashAgreeNotification::sendWechatTemplate($this->cash);
    }
}