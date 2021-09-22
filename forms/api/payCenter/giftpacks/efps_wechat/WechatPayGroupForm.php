<?php

namespace app\forms\api\payCenter\giftpacks\efps_wechat;


use app\forms\api\payCenter\EfpsWechatBasePayForm;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksGroup;
use app\plugins\giftpacks\models\GiftpacksGroupPayOrder;

class WechatPayGroupForm extends EfpsWechatBasePayForm {


    /**
     * 返回商品信息
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return array
     */
    protected function orderInfoGoods(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder){
        $payOrder = $this->getPayOrder($user, $paymentOrder);

        $group = $this->getGiftpacksGroup($payOrder);

        $giftpacks = $this->getGiftpacks($group);

        return [
            "goodsId" => $giftpacks->id,
            "name"    => $giftpacks->title,
            "price"   => $giftpacks->group_price * 100,
            "number"  => "1",
            "amount"  => $giftpacks->group_price * 100
        ];
    }

    /**
     * 获取拼单待支付记录
     * @param User $user
     * @param PaymentOrder $paymentOrder
     * @return GiftpacksGroupPayOrder
     * @throws \Exception
     */
    private function getPayOrder(User $user, PaymentOrder $paymentOrder){
        static $datas;
        if(!isset($datas[$paymentOrder->id])){
            $datas[$paymentOrder->id] = GiftpacksGroupPayOrder::findOne([
                "user_id"  => $user->id,
                "order_sn" => $paymentOrder->order_no
            ]);
            if(!$datas[$paymentOrder->id]){
                throw new \Exception("拼单待支付记录不存在");
            }
        }
        return $datas[$paymentOrder->id];
    }

    /**
     * 获取拼单信息
     * @param GiftpacksGroupPayOrder $payOrder
     * @return GiftpacksGroup
     * @throws \Exception
     */
    private function getGiftpacksGroup(GiftpacksGroupPayOrder $payOrder){
        static $datas;
        if(!isset($datas[$payOrder->group_id])){
            $datas[$payOrder->group_id] = GiftpacksGroup::findOne($payOrder->group_id);
            if(!$datas[$payOrder->group_id]){
                throw new \Exception("拼单信息不存在");
            }
        }
        return $datas[$payOrder->group_id];
    }

    /**
     * 获取大礼包
     * @param GiftpacksGroup $group
     * @return Giftpacks
     * @throws \Exception
     */
    private function getGiftpacks(GiftpacksGroup $group){
        static $datas;
        if(!isset($datas[$group->pack_id])){
            $datas[$group->pack_id] = Giftpacks::findOne($group->pack_id);
            if(!$datas[$group->pack_id] || $datas[$group->pack_id]->is_delete){
                throw new \Exception("大礼包不存在");
            }
        }
        return $datas[$group->pack_id];
    }

    /**
     * 检查能否进行支付
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return void
     */
    protected function checkBefore(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder){

        $payOrder = $this->getPayOrder($user, $paymentOrder);

        $group = $this->getGiftpacksGroup($payOrder);

        $giftpacks = $this->getGiftpacks($group);

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

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
}