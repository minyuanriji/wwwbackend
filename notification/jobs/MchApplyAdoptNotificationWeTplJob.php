<?php
namespace app\notification\jobs;


use app\notification\MchApplyAdoptNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchApplyAdoptNotificationWeTplJob extends Component implements JobInterface
{

    public $mch_apply;

    public function execute($queue)
    {
        MchApplyAdoptNotification::sendWechatTemplate($this->mch_apply);
    }
}