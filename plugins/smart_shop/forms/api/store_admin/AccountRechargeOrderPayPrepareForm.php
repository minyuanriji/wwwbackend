<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\forms\api\payCenter\paymentOrderPrepare\BasePrepareForm;
use app\models\User;
use app\plugins\smart_shop\forms\common\StorePayOrderPaidNotifyProcessForm;
use app\plugins\smart_shop\models\StorePayOrder;

class AccountRechargeOrderPayPrepareForm extends BasePrepareForm {

    private $payOrder = null;

    /**
     * 创建前检查操作
     * @param User $user
     * @return void
     * @throws \Exception
     */
    protected function checkBefore(User $user){
        $this->getPayOrder();
        if($this->payOrder->pay_status == "paid"){
            throw new \Exception("请勿重复支付");
        }
        if($this->payOrder->pay_status != "unpaid"){
            throw new \Exception("订单状态异常：" . $this->payOrder->pay_status);
        }
    }

    /**
     * 订单组，格式如下：
     *  [
     *     'total_amount' => 200.00,
     *     'content'      => '描述内容',
     *     'notify_class' => '通知操作类',
     *     'list'         => [
     *          ['amount' => 100.00, 'title' => '标题1', 'order_no' => '订单号1'],
     *          ['amount' => 100.00, 'title' => '标题2', 'order_no' => '订单号2']
     *      ]
     *  ]
     * @param User $user
     * @return array
     */
    protected function getOrderArray(User $user) {
        $this->getPayOrder();
        return [
            'total_amount' => $this->payOrder->order_price,
            'content'      => '智慧经营-门店充值订单',
            'notify_class' => StorePayOrderPaidNotifyProcessForm::class,
            'list'         => [
                ['amount' => $this->payOrder->order_price, 'title' => '智慧经营-门店充值订单', 'order_no' => $this->payOrder->order_no]
            ]
        ];
    }

    /**
     * 支付成功后的页面跳转
     * @return string
     */
    protected function getPagePath(){
        $this->getPayOrder();
        return "/smartshop/store/recharge_result?orderId=" . $this->payOrder->id;
    }

    private function getPayOrder(){
        if(!$this->payOrder){
            $this->payOrder = StorePayOrder::findOne($this->order_id);
        }
        return $this->payOrder;
    }
}