<?php
namespace app\plugins\addcredit\forms\api\order;


use app\forms\api\payCenter\paymentOrderPrepare\BasePrepareForm;
use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\forms\common\AddcreditOrderPaidNotifyProcess;
use app\plugins\hotel\models\HotelOrder;

class AddcreditOrderPayPrepareForm extends BasePrepareForm {

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
    protected function checkBefore(User $user) {
        $AddcreditOrder = AddcreditOrder::findOne(["order_no" => $this->order_no]);
        if(!$AddcreditOrder){
            throw new \Exception("订单不存在");
        }

        if($AddcreditOrder->pay_status != 'unpaid' || $AddcreditOrder->order_status != 'unpaid'){
            throw new \Exception("订单不可支付");
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
        $orderArray = ['total_amount' => 0, 'content' => '', 'notify_class' => AddcreditOrderPaidNotifyProcess::class, 'list' => []];
        $orderArray['content'] = "话费订单，订单号：" . $this->order_no;

        $orders = AddcreditOrder::find()->where(["order_no" => $this->order_no])->all();
        if(!$orders){
            throw new \Exception("订单不存在");
        }

        foreach($orders as $order){
            $orderArray['total_amount'] += $order->order_price;
            $orderArray['list'][] = [
                'amount'   => $order->order_price,
                'title'    => $order->order_no,
                'order_no' => $order->order_no
            ];
        }
        return $orderArray;
    }
}