<?php
namespace app\forms\efps;

use app\component\efps\Efps;
use app\core\payment\PaymentException;
use app\forms\common\refund\BaseRefund;
use app\models\EfpsPaymentOrder;
use app\models\PaymentRefund;
use yii\db\Exception;


class EfpsRefund extends BaseRefund{

    public function refund($paymentRefund, $paymentOrderUnion){

        $t = \Yii::$app->db->beginTransaction();
        try {

            $efpsPaymentOrder = EfpsPaymentOrder::findOne([
                "payment_order_union_id" => $paymentOrderUnion->id
            ]);

            if(!$efpsPaymentOrder){
                throw new \Exception("交易订单不存在");
            }

            $result = \Yii::$app->efps->refund([
                "customerCode" => $efpsPaymentOrder->customerCode,
                "outRefundNo"  => $paymentRefund->order_no,
                "outTradeNo"   => $efpsPaymentOrder->outTradeNo,
                "refundAmount" => $paymentRefund->amount * 100,
                "amount"       => $efpsPaymentOrder->payAmount,
                "orderInfo"    => json_decode($efpsPaymentOrder->orderInfo, true)
            ]);
            if($result["code"] == Efps::CODE_SUCCESS && $result["data"]["returnCode"] == "0000"){
                $paymentRefund->is_pay   = PaymentRefund::YES;
                $paymentRefund->pay_type = PaymentRefund::PAY_TYPE_WECHAT;
                if (!$paymentRefund->save()) {
                    throw new Exception($this->responseErrorMsg($paymentRefund));
                }
            }else{
                throw new \Exception($result['data']["returnMsg"]);
            }

            $t->commit();
            return true;
        }catch (\Exception $e){
            $t->rollBack();
            throw new PaymentException($e->getMessage());
        }
    }
}