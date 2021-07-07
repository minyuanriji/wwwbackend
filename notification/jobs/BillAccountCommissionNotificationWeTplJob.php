<?php
namespace app\notification\jobs;


use app\notification\BillAccountCommissionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class BillAccountCommissionNotificationWeTplJob extends Component implements JobInterface
{

    public $income_log;

    public function execute($queue)
    {
        BillAccountCommissionNotification::sendWechatTemplate($this->income_log);
    }
}