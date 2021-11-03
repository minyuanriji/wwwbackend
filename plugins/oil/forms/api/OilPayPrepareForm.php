<?php

namespace app\plugins\oil\forms\api;

use app\forms\api\payCenter\paymentOrderPrepare\BasePrepareForm;
use app\models\User;
use app\plugins\oil\forms\common\OilOrderPaidNotifyProcessForm;
use app\plugins\oil\models\OilOrders;

class OilPayPrepareForm extends BasePrepareForm {

    public $order_no;

    public function rules(){
        return [
            [['order_no'], 'required']
        ];
    }


    /**
     * 创建前检查操作
     * @param User $user
     * @return void
     * @throws \Exception
     */
    protected function checkBefore(User $user){
        $oilOrder = OilOrders::findOne(["order_no" => $this->order_no]);
        if(!$oilOrder){
            throw new \Exception("订单”{$this->order_no}“不存在");
        }
        if($oilOrder->pay_status != "unpaid" && $oilOrder->order_status != "unpaid"){
            throw new \Exception("订单”{$this->order_no}“无法支付");
        }
        if($oilOrder->created_at < (time() - 3600 * 12)){
            throw new \Exception("订单”{$this->order_no}“已过期");
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
    protected function getOrderArray(User $user){
        $orderArray = ['total_amount' => 0, 'content' => '', 'notify_class' => OilOrderPaidNotifyProcessForm::class, 'list' => []];
        $orderArray['content'] = "加油券订单，订单号：" . $this->order_no;

        $orders = OilOrders::find()->where(["order_no" => $this->order_no])->all();
        if(!$orders){
            throw new \Exception("订单不存在");
        }

        foreach($orders as $order){
            $orderNeedPay = $order->order_price - $order->integral_deduction_price;
            $orderArray['total_amount'] += $orderNeedPay;
            $orderArray['list'][] = [
                'amount'   => $orderNeedPay,
                'title'    => $order->order_no,
                'order_no' => $order->order_no
            ];
        }

        return $orderArray;
    }
}