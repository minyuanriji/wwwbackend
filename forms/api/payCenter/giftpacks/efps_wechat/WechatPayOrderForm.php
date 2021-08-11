<?php

namespace app\forms\api\payCenter\giftpacks\efps_wechat;

use app\forms\api\payCenter\EfpsWechatBasePayForm;
use app\models\PaymentOrder;
use app\models\PaymentOrderUnion;
use app\models\User;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class WechatPayOrderForm extends EfpsWechatBasePayForm {

    /**
     * 获取大礼包订单
     * @param PaymentOrder $paymentOrder
     * @return GiftpacksOrder
     * @throws \Exception
     */
    private function getGiftpacksOrder(PaymentOrder $paymentOrder){
        static $datas;
        if(!isset($datas[$paymentOrder->id])){
            $datas[$paymentOrder->id] = GiftpacksOrder::findOne([
                "order_sn" => $paymentOrder->order_no
            ]);
            if(!$datas[$paymentOrder->id] || $datas[$paymentOrder->id]->is_delete){
                throw new \Exception("订单不存在");
            }
        }
        return $datas[$paymentOrder->id];
    }

    /**
     * 获取大礼包
     * @return Giftpacks
     * @throws \Exception
     */
    private function getGiftpacks(GiftpacksOrder $order){
        static $datas;
        if(!isset($datas[$order->pack_id])){
            $datas[$order->pack_id] = Giftpacks::findOne($order->pack_id);
            if(!$datas[$order->pack_id] || $datas[$order->pack_id]->is_delete){
                throw new \Exception("大礼包[ID:".$order->pack_id."]不存在或已下架");
            }
        }
        return $datas[$order->pack_id];
    }


    /**
     * 返回商品信息
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return array
     */
    protected function orderInfoGoods(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder){
        $order = $this->getGiftpacksOrder($paymentOrder);
        $giftpacks = $this->getGiftpacks($order);
        return [
            "goodsId" => $giftpacks->id,
            "name"    => $giftpacks->title,
            "price"   => $giftpacks->price * 100,
            "number"  => "1",
            "amount"  => $giftpacks->price * 100
        ];
    }

    /**
     * 检查能否进行支付
     * @param User $user
     * @param PaymentOrderUnion $paymentOrderUnion
     * @param PaymentOrder $paymentOrder
     * @return void
     */
    protected function checkBefore(User $user, PaymentOrderUnion $paymentOrderUnion, PaymentOrder $paymentOrder){
        //获取订单
        $giftpacksOrder = $this->getGiftpacksOrder($paymentOrder);

        //获取大礼包
        $giftpacks = $this->getGiftpacks($giftpacksOrder);

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

        //调用支付完操作类判断是否能支付
        $processClass = $giftpacksOrder->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包订单支付完成操作类<{$processClass}>不存在");
        }
        $class = new $processClass();
        $class->checkBefore($user, $giftpacks, $giftpacksOrder);
    }
}