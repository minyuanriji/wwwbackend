<?php

namespace app\plugins\smart_shop\components\czorder_paid_notification;

use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCzorderPaidEmailJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){

    }

}