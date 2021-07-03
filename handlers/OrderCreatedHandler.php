<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 创建订单
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\models\Order;

class OrderCreatedHandler extends BaseHandler
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CREATED, function ($event) {

            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $commonOrder = OrderCommon::getCommonOrder($event->order->sign);
            $orderHandler = $commonOrder->getOrderHandler();

            $handler = $orderHandler->orderCreatedHandlerClass;
            $handler->orderConfig = $commonOrder->getOrderConfig();
            $handler->event = $event;


            $handler->setMall()->handle();
            \Yii::warning('来到订单创建事件');
        });
    }
}
