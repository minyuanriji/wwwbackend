<?php


namespace app\forms\api\payCenter\paymentOrderPrepare;


use app\forms\api\payCenter\giftpacks\CommonPayGroup;
use app\forms\api\payCenter\notifyProcess\EfpsGiftpacksGroupPaidNotifyProcessForm;
use app\models\User;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class GiftpacksGroupPrepareForm extends BasePrepareForm {

    use CommonPayGroup;

    /**
     * 创建前检查操作
     * @param User $user
     * @return void
     * @throws \Exception
     */
    protected function checkBefore(User $user){

        $group = $this->getGiftpacksGroup();

        $giftpacks = $this->getGiftpacks();

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

        $payOrder = $this->getPayOrder($user);
        if($payOrder->pay_status != "unpaid"){
            throw new \Exception("请勿重复支付操作");
        }

        $processClass = $group->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包拼单支付完成操作类<{$processClass}>不存在");
        }

        $class = new $processClass();

        $class->checkBefore($giftpacks, $group);
    }

    /**
     * 订单组
     * @param User $user
     * @return array
     */
    protected function getOrderArray(User $user){

        $giftpacks = $this->getGiftpacks();
        $payOrder  = $this->getPayOrder($user);

        $desc = "大礼包拼单记录支付[ID:".$payOrder->id."]";
        $orderArray = [
            'total_amount' => $giftpacks->group_price,
            'content'      => $desc,
            'notify_class' => EfpsGiftpacksGroupPaidNotifyProcessForm::class
        ];
        $orderArray['list'] = [
            ['amount' => $giftpacks->group_price, 'title' => $desc, 'order_no' => $payOrder->order_sn]
        ];

        return $orderArray;
    }

    /**
     * 获取支付记录
     * @param User $user
     * @return GiftpacksGroupPayOrder
     * @throws \Exception
     */
    private function getPayOrder(User $user){
        static $datas;

        $group     = $this->getGiftpacksGroup();
        $giftpacks = $this->getGiftpacks();

        if(!isset($datas[$group->id])){
            $datas[$group->id] = GiftpacksGroupPayOrder::findOne([
                "mall_id"    => $giftpacks->mall_id,
                "user_id"    => $user->id,
                "group_id"   => $group->id
            ]);
            if(!$datas[$group->id]){
                throw new \Exception("无法获取到支付记录信息");
            }
        }

        return $datas[$group->id];
    }
}