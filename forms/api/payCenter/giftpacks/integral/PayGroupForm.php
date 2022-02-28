<?php


namespace app\forms\api\payCenter\giftpacks\integral;

use app\forms\api\payCenter\giftpacks\CommonPayGroup;
use app\forms\api\payCenter\IntegralBasePayForm;
use app\models\User;
use app\plugins\giftpacks\forms\api\GiftpacksDetailForm;

class PayGroupForm extends IntegralBasePayForm {

    use CommonPayGroup;

    /**
     * 获取要支付的金豆数
     * @param User $user
     * @return float
     */
    protected function payIntegray(User $user){
        //注意返回的是拼团对应的金豆价
        return GiftpacksDetailForm::groupIntegralDeductionPrice($this->getGiftpacks(), $user);
    }

    /**
     * 支付后操作
     * @param User $user
     * @param Closure $callback
     * @return array
     */
    public function paidAction(User $user, \Closure $callback){

        $group = $this->getGiftpacksGroup();

        $giftpacks = $this->getGiftpacks();

        //检查是否支持金豆支付
        if($giftpacks->allow_currency != "integral"){
            throw new \Exception("不允许使用金豆支付");
        }

        $processClass = $group->process_class;
        if(!class_exists($processClass)){
            throw new \Exception("大礼包拼单支付完成操作类<{$processClass}>不存在");
        }

        $class = new $processClass([
            'pay_type'                 => 'integral',
            'integral_deduction_price' => $this->payIntegray($user),
            'integral_fee_rate'        => 0
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