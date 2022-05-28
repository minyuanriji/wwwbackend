<?php

namespace app\plugins\perform_distribution\handlers;

use app\handlers\BaseHandler;
use app\plugins\perform_distribution\events\AwardOrderEvent;
use app\plugins\perform_distribution\jobs\AwardOrderCreateJob;

class AwardOrderHandler extends BaseHandler{

    /**
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register(){
        \Yii::$app->on(AwardOrderEvent::SHOP_ORDER_PAID, function (AwardOrderEvent $event) {
            \Yii::$app->queue->delay(5)->push(new AwardOrderCreateJob([
                'order' => $event->order
            ]));
        });
    }
}