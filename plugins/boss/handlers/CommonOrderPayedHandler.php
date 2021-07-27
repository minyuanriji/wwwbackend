<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-12
 * Time: 9:44
 */

namespace app\plugins\boss\handlers;


use app\handlers\BaseHandler;
use app\models\Order;
use app\plugins\boss\jobs\CommonOrderPayedJob;
use yii\base\Event;

class CommonOrderPayedHandler extends BaseHandler
{


    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.
        \Yii::$app->on(Order::EVENT_PAYED, function ($event) {
            $order_type = $event->order_type ?? 0;
            $order_id = $event->order->id;
            \Yii::$app->queue->delay(1)->push(new CommonOrderPayedJob([
                'order_type' => $order_type,
                'order_id' => $order_id
            ]));
        });
    }
}