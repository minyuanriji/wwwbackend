<?php
namespace app\notification\jobs;


use app\notification\RevenueRecordCommissionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class RevenueRecordCommissionNotificationWeTplJob extends Component implements JobInterface
{

    public $income_log;

    public function execute($queue)
    {
        RevenueRecordCommissionNotification::sendWechatTemplate($this->income_log);
    }
}