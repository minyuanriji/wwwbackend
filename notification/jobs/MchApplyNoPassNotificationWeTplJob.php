<?php
namespace app\notification\jobs;


use app\notification\MchApplyNoPassNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchApplyNoPassNotificationWeTplJob extends Component implements JobInterface
{

    public $mch;

    public function execute($queue)
    {
        MchApplyNoPassNotification::sendWechatTemplate($this->mch);
    }
}