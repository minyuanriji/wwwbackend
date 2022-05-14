<?php

namespace app\plugins\smart_shop\components\jobs;

use yii\base\Component;
use yii\queue\JobInterface;

class NotificationCyorderPaidMobileJob extends Component implements JobInterface{

    public $mall_id;
    public $order_id;

    public function execute($queue){

    }
}