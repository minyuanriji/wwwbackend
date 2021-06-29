<?php
namespace app\notification\jobs;


use app\notification\MchApplyPassedNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchApplyPassedNotificationWeTplJob extends Component implements JobInterface
{

    public $mch;

    public function execute($queue)
    {
        MchApplyPassedNotification::sendWechatTemplate($this->mch);
    }
}