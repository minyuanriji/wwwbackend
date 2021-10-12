<?php

namespace app\notification\jobs;

use app\notification\MchCheckoutOrderPaySuccessNotification;
use yii\base\Component;
use yii\queue\JobInterface;

class MchCheckoutOrderPaySuccessNotificationWeTplJob extends Component implements JobInterface
{
    public $mchCheckoutOrder;

    public function execute($queue)
    {
        MchCheckoutOrderPaySuccessNotification::sendWechatTemplate($this->mchCheckoutOrder);
    }
}