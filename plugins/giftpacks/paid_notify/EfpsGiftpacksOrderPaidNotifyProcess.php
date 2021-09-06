<?php
namespace app\plugins\giftpacks\paid_notify;

use app\core\payment\PaymentNotify;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class EfpsGiftpacksOrderPaidNotifyProcess extends PaymentNotify{

    /**
     * @param app\core\payment\PaymentOrder $paymentOrder
     * @return void
     * @throws \Exception
     */
    public function notify($paymentOrder){
        $giftpacksOrder = GiftpacksOrder::findOne([
            "order_sn" => $paymentOrder->orderNo
        ]);
        if(!$giftpacksOrder || $giftpacksOrder->pay_status != "unpaid"){
            return;
        }

        //大礼包
        $giftpacks = Giftpacks::findOne($giftpacksOrder->pack_id);
        if(!$giftpacks){
            return;
        }

        $processClass = $giftpacksOrder->process_class;
        if(!class_exists($processClass)){
            return;
        }

        try {
            $class = new $processClass([
                'pay_type'  => 'money',
                'pay_price' => $paymentOrder->amount
            ]);

            $class->doProcess($giftpacks, $giftpacksOrder);
        }catch (\Exception $e){
            throw $e;
        }

    }

}