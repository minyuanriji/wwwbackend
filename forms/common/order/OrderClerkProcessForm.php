<?php

namespace app\forms\common\order;


use app\forms\common\CommonClerkProcessForm;
use app\models\clerk\ClerkData;
use app\models\Order;

class OrderClerkProcessForm extends CommonClerkProcessForm {

    /**
     * 核销处理
     * @param ClerkData $clerkData
     * @throws \Exception
     */
    public function process(ClerkData $clerkData){
        $order = Order::find()->with(['detail.goods.goodsWarehouse'])->where([
            "id" => $clerkData->source_id
        ])->one();
        if (!$order) {
            throw new \Exception('订单不存在');
        }

        if ($order->status == 0) {
            throw new \Exception('订单进行中，不能进行操作');
        }

        if ($order->cancel_status == 2) {
            throw new \Exception('订单申请退款中');
        }

        if ($order->cancel_status == 1) {
            throw new \Exception('订单已退款');
        }

        if ($order->is_pay != 1) {
            throw new \Exception('订单未支付，请先进行收款');
        }

        if(!empty($order->clerk_id)){
            throw new \Exception('请勿重复核销操作');
        }

        throw new \Exception('功能开发中~');
    }
}