<?php
namespace app\helpers;

use app\forms\common\UserBalanceModifyForm;
use app\forms\efps\EfpsRefund;
use app\models\BalanceLog;
use app\models\PaymentOrder;
use app\models\PaymentRefund;
use app\models\PaymentRefundNew;
use app\models\User;

class PaymentRefundHelper{

    /**
     * 统一退款操作
     * @param User $user
     * @param PaymentOrder $paymentOrder
     * @param $source_type
     * @param $source_id
     * @param $desc
     * @throws \app\core\payment\PaymentException
     */
    public static function doRefund(User $user, PaymentOrder $paymentOrder, $source_type, $source_id, $desc){

        if(!$paymentOrder->is_pay){
            throw new \Exception("支付单状态未付款");
        }

        $paymentOrderUnion = $paymentOrder->paymentOrderUnion;

        $paymentRefund = PaymentRefundNew::findOne(['payment_order_id' => $paymentOrder->id]);
        if(!$paymentRefund){
            $paymentRefund = new PaymentRefundNew();
            $paymentRefund->mall_id          = $paymentOrderUnion->mall_id;
            $paymentRefund->payment_order_id = $paymentOrder->id;
            $paymentRefund->user_id          = $user->id;
            $paymentRefund->amount           = $paymentOrder->amount;
            $paymentRefund->order_no         = "PR" . date("ymdhis") . rand(100000, 999999);
            $paymentRefund->is_pay           = 0;
            $paymentRefund->pay_type         = $paymentOrder->pay_type;
            $paymentRefund->created_at       = time();
        }

        if (!empty($paymentRefund) && $paymentRefund->is_pay == PaymentRefund::YES){
            throw new \Exception('售后订单已打款！无需重复');
        }

        //退款金额小于0，直接返回
        if($paymentRefund->amount <= 0)
            return;

        $paymentRefund->is_pay = PaymentRefund::YES;
        if(!$paymentRefund->save()){
            throw new \Exception(json_encode($paymentRefund->getErrors()));
        }

        $paymentOrder->refund = $paymentRefund->amount;
        $paymentOrder->updated_at = time();
        if(!$paymentOrder->save()){
            throw new \Exception(json_encode($paymentOrder->getErrors()));
        }

        if($paymentRefund->pay_type == 3){ //退余额
           $modifyForm = new UserBalanceModifyForm([
               "type"        => BalanceLog::TYPE_ADD,
               "money"       => $paymentRefund->amount,
               "custom_desc" => "",
               "source_id"   => $source_id,
               "source_type" => $source_type,
               "desc"        => $desc
           ]);
            $modifyForm->modify($user);
        }else{ //微信、支付宝
            $efpsRefund = new EfpsRefund();
            $efpsRefund->refund($paymentRefund, $paymentOrderUnion);
        }
    }

}