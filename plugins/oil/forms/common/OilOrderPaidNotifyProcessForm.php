<?php

namespace app\plugins\oil\forms\common;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\oil\models\OilOrders;

class OilOrderPaidNotifyProcessForm extends PaymentNotify{

    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        try {
            $oilOrder = OilOrders::findOne(["order_no" => $paymentOrder->orderNo]);
            if (!$oilOrder) {
                throw new \Exception("加油券订单{$paymentOrder->orderNo}不存在");
            }

            if($oilOrder->pay_status != "unpaid" && $oilOrder->order_status != "unpaid"){
                throw new \Exception("订单{$paymentOrder->orderNo}状态异常");
            }

            //更新订单
            switch ($paymentOrder->payType) {
                case PaymentOrder::PAY_TYPE_BALANCE:
                    $oilOrder->pay_type = "balance";
                    break;
                case PaymentOrder::PAY_TYPE_WECHAT:
                    $oilOrder->pay_type = "wechat";
                    break;
                case PaymentOrder::PAY_TYPE_ALIPAY:
                    $oilOrder->pay_type = "alipay";
                    break;
                default:
                    break;
            }

            $oilOrder->order_status = "unconfirmed";
            $oilOrder->pay_status   = "paid";
            $oilOrder->pay_at       = date("Y-m-d H:i:s");
            $oilOrder->pay_price    = $paymentOrder->amount;
            if (!$oilOrder->save()) {
                throw new \Exception($this->responseErrorMsg($oilOrder));
            }
        }catch (\Exception $e){
            //file_put_contents(__DIR__ . "/debug", $e->getMessage());
            throw $e;
        }
    }
}