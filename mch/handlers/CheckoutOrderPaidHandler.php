<?php
namespace app\mch\handlers;


use app\mch\events\CheckoutOrderPaidEvent;
use app\mch\forms\order\CheckoutOrderDeductIntegralForm;
use app\mch\forms\order\CheckoutOrderAutoSettleForm;
use app\models\Mall;

class CheckoutOrderPaidHandler {

    public static function handle(CheckoutOrderPaidEvent $event){

        $checkoutOrder = $event->checkoutOrder;
        $amount        = $event->amount;

        $mall = Mall::findOne([
            'id'         => $checkoutOrder->mall_id,
            'is_delete'  => 0,
            'is_recycle' => 0,
        ]);

        \Yii::$app->setMallId($mall->id);
        \Yii::$app->setMall($mall);

        if(!$checkoutOrder->is_pay){

            $t = \Yii::$app->db->beginTransaction();
            try {
                $checkoutOrder->pay_price  = $amount;
                $checkoutOrder->is_pay     = 1;
                $checkoutOrder->pay_at     = time();
                $checkoutOrder->updated_at = time();
                if(!$checkoutOrder->save()){
                    throw new \Exception('保存结账单失败');
                }

                //购物券抵扣
                $deductIntegralForm = new CheckoutOrderDeductIntegralForm([
                    "user_id"         => $checkoutOrder->pay_user_id,
                    "deduction_price" => $checkoutOrder->integral_deduction_price,
                    "source_id"       => $checkoutOrder->id,
                    "desc"            => "商家结账单(" . $checkoutOrder->id . ")付款",
                    "source_table"    => "plugin_mch_checkout_order"
                ]);
                if(!$deductIntegralForm->save()){
                    throw new \Exception(CheckoutOrderDeductIntegralForm::$errorMsg);
                }

                //商家结算
                $settleForm = new CheckoutOrderAutoSettleForm();
                $settleForm->save();

                $t->commit();
            }catch (\Exception $e){
                $t->rollBack();
                throw new \Exception($e->getMessage());
            }
        }
    }


}