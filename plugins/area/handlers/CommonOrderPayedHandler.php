<?php

namespace app\plugins\area\handlers;

use app\handlers\BaseHandler;
use app\models\Order;
use app\plugins\area\jobs\CommonOrderPayedJob;
use yii\base\Event;

class CommonOrderPayedHandler extends BaseHandler
{
    /**
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.
        /*\Yii::$app->on(Order::EVENT_PAYED, function ($event) {

            $order_type = $event->order_type ?? 0;
            $order_id = $event->order->id;
            \Yii::$app->queue->delay(1)->push(new CommonOrderPayedJob([
                'order_type' => $order_type,
                'order_id' => $order_id
            ]));
        });*/
    }
}