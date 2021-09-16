<?php

namespace app\plugins\alibaba\forms\payCenter;

use app\forms\api\payCenter\paymentOrderPrepare\BasePrepareForm;
use app\models\User;
use app\plugins\alibaba\models\AlibabaDistributionOrder;
use app\plugins\alibaba\notify_class\AlibabaDistributionOrderNotifiyProcess;

class AlibabaDistributionOrderPrepareForm extends BasePrepareForm {

    public $token;
    public $id;

    public function rules(){
        return [
            [['token'], 'string'],
            [['id'], 'integer']
        ];
    }

    /**
     * 创建前检查操作
     * @param User $user
     * @return void
     * @throws \Exception
     */
    protected function checkBefore(User $user){
        if(!$this->token && !$this->id){
            throw new \Exception("参数丢失");
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

        $orderArray = ['total_amount' => 0, 'content' => '', 'notify_class' => AlibabaDistributionOrderNotifiyProcess::class, 'list' => []];

        $where['is_delete'] = 0;
        $where['is_recycle'] = 0;
        if($this->id){
            $where["id"] = $this->id;
            $orderArray['content'] = "社交电商订单，订单ID：" . $this->id;
        }else{
            $where['token'] = $this->token;
            $orderArray['content'] = "社交电商订单，批次号：" . $this->token;
        }
        $orders = AlibabaDistributionOrder::find()->where($where)->all();
        if(!$orders){
            throw new \Exception("订单不存在");
        }

        foreach($orders as $order){
            $orderArray['total_amount'] += $order->total_price;
            $orderArray['list'][] = [
                'amount'   => $order->total_price,
                'title'    => $order->order_no,
                'order_no' => $order->order_no
            ];
        }

        return $orderArray;
    }
}