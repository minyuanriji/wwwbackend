<?php
namespace app\mch\payment;


use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;

class CheckoutOrderPayNotify extends PaymentNotify{

    /**
    * @param PaymentOrder $paymentOrder
    * @return mixed
    */
    public function notify($paymentOrder){

        return true;
    }
}