<?php

namespace app\forms\api\payCenter\giftpacks\integral;

use app\forms\api\payCenter\giftpacks\CommonPayOrder;
use app\forms\api\payCenter\IntegralBasePayForm;
use app\models\User;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;

class PayOrderForm extends IntegralBasePayForm {

    use CommonPayOrder;

    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback 回掉方法传入红包记录来源ID、类型、描述
     * @return array
     */
    protected function paidAction(User $user, \Closure $callback){

        $order = $this->getGiftpacksOrder();
        $giftpacks = $this->getGiftpacks();

        //检查是否支持红包支付
        if($giftpacks->allow_currency != "integral"){
            throw new \Exception("不允许使用红包支付");
        }

        $processClass = $order->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包订单支付完成操作类<{$processClass}>不存在");
        }

        $class = new $processClass([
            'pay_type'                 => 'integral',
            'integral_deduction_price' => $this->payIntegray($user),
            'integral_fee_rate'        => 0
        ]);

        $class->checkBefore($user, $giftpacks, $order);

        $class->doProcess($giftpacks, $order);

        //执行回掉函数
        $desc = "支付大礼包订单[ID:".$order->id."]";
        $callback($order->id, "giftpacks_order", $desc);

        return [];
    }

    /**
     * 获取要支付的红包数
     * @param User $user
     * @return float
     */
    protected function payIntegray(User $user){
        return GiftpacksDetailForm::integralDeductionPrice($this->getGiftpacks(), $user);
    }

}