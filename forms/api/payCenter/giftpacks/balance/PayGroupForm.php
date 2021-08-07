<?php


namespace app\forms\api\payCenter\giftpacks\balance;


use app\forms\api\payCenter\BalanceBasePayForm;
use app\forms\api\payCenter\giftpacks\CommonPayGroup;
use app\models\User;

class PayGroupForm extends BalanceBasePayForm {

    use CommonPayGroup;

    /**
     * 获取要支付的余额
     * @param User $user
     * @return float
     */
    protected function payBalance(User $user){
        //注意返回的是拼团价
        return $this->getGiftpacks()->group_price;
    }

    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback 回掉方法传入红包记录来源ID、类型、描述
     * @return array
     */
    protected function paidAction(User $user, \Closure $callback){

        $group = $this->getGiftpacksGroup();

        $giftpacks = $this->getGiftpacks();

        //检查是否支持现金支付
        if($giftpacks->allow_currency != "money"){
            throw new \Exception("不允许使用现金支付");
        }

        $processClass = $group->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包拼单支付完成操作类<{$processClass}>不存在");
        }

        $class = new $processClass([
            'pay_type'  => 'balance',
            'pay_price' => $this->payBalance($user)
        ]);

        $class->checkBefore($giftpacks, $group);

        $class->doProcess($user, $giftpacks, $group);

        //执行回掉函数
        $desc = "大礼包拼单记录支付[ID:".$class->getPayOrder()->id."]";
        $callback($class->getPayOrder()->id, "giftpacks_group_payorder", $desc);

        return [
            "finished" => $class->getFinished(),
            "group_id" => (int)$this->group_id,
            "order_id" => $class->getOrderId()
        ];
    }
}