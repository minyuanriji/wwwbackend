<?php

namespace app\forms\api\payCenter\giftpacks\balance;

use app\forms\api\payCenter\BalanceBasePayForm;
use app\forms\api\payCenter\giftpacks\CommonPayOrder;
use app\models\User;

class PayOrderForm extends BalanceBasePayForm{

    use CommonPayOrder;

    /**
     * 获取要支付的余额
     * @param User $user
     * @return float
     */
    protected function payBalance(User $user){
        return $this->getGiftpacksOrder()->order_price;
    }

    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback 回掉方法传入金豆记录来源ID、类型、描述
     * @return array
     */
    protected function paidAction(User $user, \Closure $callback){
        $order = $this->getGiftpacksOrder();
        $giftpacks = $this->getGiftpacks();

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

        $processClass = $order->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包订单支付完成操作类<{$processClass}>不存在");
        }

        $class = new $processClass([
            'pay_type'  => 'balance',
            'pay_price' => $this->payBalance($user)
        ]);

        $class->checkBefore($user, $giftpacks, $order);

        $class->doProcess($giftpacks, $order);

        //执行回掉函数
        $desc = "支付大礼包订单[ID:".$order->id."]";
        $callback($order->id, "giftpacks_order", $desc);

        return [];
    }
}