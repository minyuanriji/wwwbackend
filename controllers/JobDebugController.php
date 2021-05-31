<?php
namespace app\controllers;

use yii\web\Controller;

class JobDebugController extends Controller{

    public function actionIndex(){

/*        try {
            $paymentOrder = PaymentOrder::findOne([
                "order_no" => "MS202105191329222619372952"
            ]);

            //获取到结账单
            $checkoutOrder = MchCheckoutOrder::findOne([
                'order_no'  => $paymentOrder->order_no,
                'is_delete' => 0
            ]);
            if(!$checkoutOrder){
                throw new \Exception("无法获取到结帐单");
            }

            $event = new CheckoutOrderPaidEvent();
            $event->checkoutOrder = $checkoutOrder;
            $event->amount        = $paymentOrder->amount;
            $event->sender        = $this;
            \Yii::$app->trigger(MchCheckoutOrder::EVENT_PAYED, $event);
        }catch (\Exception $e){
            echo $e->getMessage();
        }*/



    }

}