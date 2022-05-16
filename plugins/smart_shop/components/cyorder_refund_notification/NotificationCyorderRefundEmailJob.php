<?php

namespace app\plugins\smart_shop\components\cyorder_refund_notification;

use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderRefundEmailJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){

    }

}