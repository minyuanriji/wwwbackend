<?php

namespace app\plugins\smart_shop\forms\common;

use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\smart_shop\models\StoreAccount;
use app\plugins\smart_shop\models\StorePayOrder;

class StorePayOrderPaidNotifyProcessForm extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){
        try {
            $payOrder = StorePayOrder::findOne(["order_no" => $paymentOrder->orderNo]);
            if(!$payOrder){
                throw new \Exception("数据异常，订单不存在");
            }
            if($payOrder->pay_status == "unpaid"){

                //更新订单
                switch ($paymentOrder->payType) {
                    case PaymentOrder::PAY_TYPE_BALANCE:
                        $payOrder->pay_type = "balance";
                        break;
                    case PaymentOrder::PAY_TYPE_WECHAT:
                        $payOrder->pay_type = "wechat";
                        break;
                    case PaymentOrder::PAY_TYPE_ALIPAY:
                        $payOrder->pay_type = "alipay";
                        break;
                    default:
                        break;
                }
                $payOrder->order_status = "success";
                $payOrder->pay_status   = "paid";
                $payOrder->pay_price    = $paymentOrder->amount;
                $payOrder->pay_time     = time();
                $payOrder->pay_uid      = $paymentOrder->user_id;
                if (!$payOrder->save()) {
                    throw new \Exception($this->responseErrorMsg($payOrder));
                }

                //业务逻辑处理
                if($payOrder->business_scene == "shopping_voucher"){
                    $this->businessShoppingVoucher($payOrder); //红包储值
                }
            }

        }catch (\Exception $e){
            throw $e;
        }
    }

    //红包储值业务
    private function businessShoppingVoucher(StorePayOrder $payOrder){
        $account = StoreAccount::findOne([
            "mall_id"     => $payOrder->mall_id,
            "ss_mch_id"   => $payOrder->ss_mch_id,
            "ss_store_id" => $payOrder->ss_store_id
        ]);
        if(!$account){
            $account = new StoreAccount([
                "mall_id"     => $payOrder->mall_id,
                "ss_mch_id"   => $payOrder->ss_mch_id,
                "ss_store_id" => $payOrder->ss_store_id,
                "created_at"  => time(),
                "updated_at"  => time(),
                "balance"     => 0
            ]);
        }
        $modifyForm = new StoreAccountBalanceModifyForm([
            "source_type" => "store_pay_order",
            "source_id"   => $payOrder->id,
            "balance"     => $payOrder->order_price,
            "desc"        => "智慧经营-门店红包储值"
        ]);
        $modifyForm->add($account);
    }
}