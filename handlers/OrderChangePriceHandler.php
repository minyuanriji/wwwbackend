<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 改变订单价格
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers;

use app\events\OrderEvent;
use app\forms\common\order\OrderCommon;
use app\models\Order;

class OrderChangePriceHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(Order::EVENT_CHANGE_PRICE, function ($event) {
            /** @var OrderEvent $event */
            $commonOrder = OrderCommon::getCommonOrder($event->order->sign);
            $orderHandler = $commonOrder->getOrderHandler();
            $handler = $orderHandler->orderChangePriceHandlerClass;
            $handler->orderConfig = $commonOrder->getOrderConfig();
            $handler->event = $event;
            $handler->setMchId()->setMall()->handle();
        });
    }
}
