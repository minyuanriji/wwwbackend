<?php
namespace app\notification\jobs;


use app\notification\GoodsCommissionNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class GoodsCommissionNotificationWeTplJob extends Component implements JobInterface
{

    public $income_log;

    public function execute($queue)
    {
        GoodsCommissionNotification::sendWechatTemplate($this->income_log);
    }
}