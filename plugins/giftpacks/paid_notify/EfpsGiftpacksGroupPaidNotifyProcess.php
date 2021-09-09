<?php

namespace app\plugins\giftpacks\paid_notify;

use app\core\payment\PaymentNotify;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class EfpsGiftpacksGroupPaidNotifyProcess extends PaymentNotify{

    /**
     * @param \app\core\payment\PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $groupPayOrder = GiftpacksGroupPayOrder::findOne(["order_sn" => $paymentOrder->orderNo]);
        if(!$groupPayOrder || $groupPayOrder->pay_status != "unpaid"){
            return;
        }

        //获取拼单
        $group = GiftpacksGroup::findOne($groupPayOrder->group_id);
        if(!$group){
            return;
        }

        //支付用户
        $user = User::findOne($groupPayOrder->user_id);
        if(!$user){
            return;
        }

        //大礼包
        $giftpacks = Giftpacks::findOne($group->pack_id);

        try {
            $processClass = $group->process_class;
            if(!class_exists($processClass)){
                throw new \Exception("大礼包拼单支付完成操作类<{$processClass}>不存在");
            }

            $class = new $processClass([
                'pay_type'  => 'money',
                'pay_price' => $paymentOrder->amount
            ]);

            $class->doProcess($user, $giftpacks, $group, $groupPayOrder);
        }catch (\Exception $e){
            throw $e;
        }
    }
}