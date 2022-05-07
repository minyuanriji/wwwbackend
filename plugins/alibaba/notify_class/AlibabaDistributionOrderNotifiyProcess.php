<?php

namespace app\plugins\alibaba\notify_class;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\models\UserAddress;
use app\plugins\alibaba\exception\AlibabaOrderException;
use app\plugins\alibaba\forms\api\AlibabaDistributionOrderForm;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail;
use app\plugins\alibaba\models\AlibabaDistributionOrderDetail1688;

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

        try {
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

            $userAddress = UserAddress::findOne($order->address_id);

            //通知阿里巴巴下单
            $orderDetails = AlibabaDistributionOrderDetail::find()->where([
                "order_id" => $order->id
            ])->all();
            foreach($orderDetails as $orderDetail){
                $exists = AlibabaDistributionOrderDetail1688::find()->where(["order_detail_id" => $orderDetail->id])->exists();
                if(!$exists){ //生成1688订单
                    AlibabaDistributionOrderForm::createAliOrder($order, $orderDetail, $userAddress);
                }
            }
        }catch (\Exception $e){
            $ex = new AlibabaOrderException($e->getMessage(), $e->getCode(), $e->getPrevious());
            $ex->mall_id  = $order->mall_id;
            $ex->order_id = $order->id;
            throw $ex;
        }

        return true;
    }
}