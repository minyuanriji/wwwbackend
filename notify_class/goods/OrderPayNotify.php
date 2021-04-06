<?php
namespace app\notify_class\goods;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\events\OrderEvent;
use app\models\CommonOrder;
use app\models\Order;

class OrderPayNotify extends PaymentNotify{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){
        /** @var Order $order */
        $order = Order::findOne([
            'order_no' => $paymentOrder->orderNo,
        ]);
        if (!$order) {
            return false;
        }
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
        return true;
    }
}