<?php

namespace app\plugins\alibaba\notify_class;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrder;

class AlibabaDistributionOrderNotifiyProcess extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $order = AlibabaDistributionOrder::findOne([
            "order_no" => $paymentOrder->orderNo
        ]);

        if(!$order || $order->is_pay){
            return;
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
        $order->pay_at = time();
        $order->save();

        return true;
    }
}