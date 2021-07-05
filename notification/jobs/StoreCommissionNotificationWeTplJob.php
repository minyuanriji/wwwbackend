<?php
namespace app\notification\jobs;


use app\notification\StoreCommissionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class StoreCommissionNotificationWeTplJob extends Component implements JobInterface
{

    public $income_log;

    public function execute($queue)
    {
        StoreCommissionNotification::sendWechatTemplate($this->income_log);
    }
}