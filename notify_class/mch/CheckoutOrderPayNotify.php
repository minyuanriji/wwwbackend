<?php
namespace app\notify_class\mch;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\mch\events\CheckoutOrderPaidEvent;
use app\plugins\mch\models\MchCheckoutOrder;

class CheckoutOrderPayNotify extends PaymentNotify{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        //获取到结账单
        $checkoutOrder = MchCheckoutOrder::findOne([
            'order_no'  => $paymentOrder->orderNo,
            'is_delete' => 0
        ]);
        if(!$checkoutOrder)
            return false;

        $event = new CheckoutOrderPaidEvent();
        $event->checkoutOrder = $checkoutOrder;
        $event->amount        = $paymentOrder->amount;
        $event->sender        = $this;
        \Yii::$app->trigger(MchCheckoutOrder::EVENT_PAYED, $event);

        return true;
    }
}