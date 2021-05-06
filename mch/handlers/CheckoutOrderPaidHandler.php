<?php
namespace app\mch\handlers;


use app\core\ApiCode;
use app\mch\events\CheckoutOrderPaidEvent;
use app\mch\forms\order\CheckoutOrderDeductIntegralForm;
use app\models\Mall;
use app\forms\efps\distribute\EfpsDistributeForm;
use app\models\Store;

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

                //获取门店名称
                $store_name = Store::find()->where([
                    'id'            => $checkoutOrder->store_id,
                    'is_delete'     => 0,
                ])->one();


                //红包券抵扣
                if($checkoutOrder->integral_deduction_price > 0){
                    $deductIntegralForm = new CheckoutOrderDeductIntegralForm([
                        "user_id"           => $checkoutOrder->pay_user_id,
                        "deduction_price"   => $checkoutOrder->integral_deduction_price,
                        "source_id"         => $checkoutOrder->id,
                        "desc"              => "商家" . $store_name->name . "结账单(" . $checkoutOrder->id . ")付款",
                        "source_table"      => "plugin_mch_checkout_order"
                    ]);
                    if(!$deductIntegralForm->save()){
                        throw new \Exception(CheckoutOrderDeductIntegralForm::$errorMsg);
                    }
                }

                //分账业务
                $res = EfpsDistributeForm::checkoutOrder($checkoutOrder);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }

                $t->commit();
            }catch (\Exception $e){
                $t->rollBack();
                throw new \Exception($e->getMessage());
            }
        }
    }

}