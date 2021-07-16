<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单销售量
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\models\Order;

class OrderSalesHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_SALES, function ($event) {
            /** @var OrderEvent $event */
            \Yii::$app->setMchId($event->order->mch_id);
            $commonOrder = OrderCommon::getCommonOrder($event->order->sign);
            $orderHandler = $commonOrder->getOrderHandler();
            $handler = $orderHandler->orderSalesHandlerClass;
            $handler->orderConfig = $commonOrder->getOrderConfig();
            $handler->event = $event;
            $handler->setMall()->handle();
        });
    }
}
