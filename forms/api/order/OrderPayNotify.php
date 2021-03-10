<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单支付回调类
 * Author: zal
 * Date: 2020-04-18
 * Time: 09:49
 */

namespace app\forms\api\order;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\events\OrderEvent;
use app\models\CommonOrder;
use app\models\Order;

class OrderPayNotify extends PaymentNotify
{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder)
    {
        if(substr($paymentOrder->orderNo, 0, 2) == "SS"){
            $orders = Order::find()->where([
                "same_order_no" => $paymentOrder->orderNo
            ])->all();
        }else{
            $orders = Order::find()->where([
                "order_no" => $paymentOrder->orderNo
            ])->all();
        }

        if(!$orders) return false;

        foreach($orders as $order){
            $order->is_pay = 1;
            switch ($paymentOrder->payType) {
                case PaymentOrder::PAY_TYPE_HUODAO:
                    $order->is_pay = 0;
                    $order->pay_type = 2;
                    break;
                case PaymentOrder::PAY_TYPE_BALANCE:
                    $order->pay_type = 3;
                    break;
                case PaymentOrder::PAY_TYPE_WECHAT:
                    $order->pay_type = 1;
                    break;
                case PaymentOrder::PAY_TYPE_ALIPAY:
                    $order->pay_type = 4;
                    break;
                case PaymentOrder::PAY_TYPE_BAIDU:
                    $order->pay_type = 5;
                    break;
                case PaymentOrder::PAY_TYPE_TOUTIAO:
                    $order->pay_type = 6;
                    break;
                default:
                    break;
            }
            $order->status = Order::STATUS_WAIT_DELIVER;
            $order->pay_at = time();
            $order->save();
            $event = new OrderEvent();
            $event->order = $order;
            $event->sender = $this;
            $event->order_type = CommonOrder::ORDER_TYPE_MALL_GOODS;
            \Yii::$app->trigger(Order::EVENT_PAYED, $event);
        }

        return true;
    }
}
