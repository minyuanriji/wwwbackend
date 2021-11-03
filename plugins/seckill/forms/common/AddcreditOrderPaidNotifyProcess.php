<?php

namespace app\plugins\addcredit\forms\common;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\addcredit\models\AddcreditOrder;

class AddcreditOrderPaidNotifyProcess extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $AddcreditOrder = AddcreditOrder::findOne(["order_no" => $paymentOrder->orderNo]);
        if(!$AddcreditOrder){
            throw new \Exception("订单{$paymentOrder->orderNo}不存在");
        }

        if($AddcreditOrder->pay_status != 'unpaid' || $AddcreditOrder->order_status != 'unpaid'){
            throw new \Exception("订单{$paymentOrder->orderNo}状态异常");
        }

        //更新订单状态为已支付
        $AddcreditOrder->order_status = "processing";
        $AddcreditOrder->pay_status = "paid";
        $AddcreditOrder->pay_at = time();
        $AddcreditOrder->plateform_request_data = "";
        $AddcreditOrder->plateform_response_data = "";
        if (!$AddcreditOrder->save()) {
            throw new \Exception($this->responseErrorMsg($AddcreditOrder));
        }
    }
}