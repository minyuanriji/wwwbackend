<?php
namespace app\notification\jobs;


use app\notification\AddOfflineNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class AddOfflineNotificationWeTplJob extends Component implements JobInterface
{

    public $user;

    public function execute($queue)
    {
        AddOfflineNotification::sendWechatTemplate($this->user);
    }
}