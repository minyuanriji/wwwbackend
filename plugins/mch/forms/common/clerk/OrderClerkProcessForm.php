<?php

namespace app\plugins\mch\forms\common\clerk;

use app\events\OrderEvent;
use app\forms\common\CommonClerkProcessForm;
use app\models\clerk\ClerkData;
use app\models\Order;
use app\models\OrderClerk;
use app\models\Store;
use app\plugins\mch\models\Mch;

class OrderClerkProcessForm extends CommonClerkProcessForm
{
    /**
     * 检查订单
     * @param Order $order
     * @throws \Exception
     */
    public static function checkOrder(Order $order){
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
            throw new \Exception('订单未支付');
        }

        if(!empty($order->clerk_id)){
            throw new \Exception('请勿重复核销操作');
        }
    }

    /**
     * 检查核销权限
     * @param $clerkUserId
     * @param Mch $mch
     * @throws \Exception
     */
    public static function checkAuth($clerkUserId, Mch $mch){
        if($clerkUserId != $mch->user_id){
            throw new \Exception("[ID:".$clerkUserId."]无权限核销");
        }
    }

    /**
     * 核销订单
     * @param integer $clerkUserId 核销人员的用户ID
     * @param Store $store 在哪一家门店核销
     * @param Order $order 要核销的订单
     * @throws \Exception
     */
    public static function clerkOrder($clerkUserId, Store $store, Order $order){
        $order->is_send     = 1;
        $order->send_at     = time();
        $order->is_confirm  = 1;
        $order->confirm_at  = time();
        $order->clerk_id    = $clerkUserId;
        $order->store_id    = $store->id;

        if (!$order->save()) {
            throw new \Exception(json_encode($order->getErrors()));
        }

        $orderClerk = OrderClerk::find()->where(['order_id' => $order->id])->one();
        if (!$orderClerk) {
            $orderClerk = new OrderClerk();
            $orderClerk->mall_id          = \Yii::$app->mall->id;
            $orderClerk->affirm_pay_type  = 1;
            $orderClerk->order_id         = $order->id;
        }
        $orderClerk->clerk_remark = '';
        $orderClerk->clerk_type   = 1;
        if (!$orderClerk->save()) {
            throw new \Exception(json_encode($orderClerk->getErrors()));
        }

        \Yii::$app->trigger(Order::EVENT_CONFIRMED, new OrderEvent([
            'order' => $order
        ]));
    }

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

        //检查订单
        static::checkOrder($order);

        //获取订单所属商户
        $mch = Mch::findOne($order->mch_id);
        if(!$mch || $mch->is_delete){
            throw new \Exception("商户[ID:".$order->mch_id."]不存在");
        }

        //获取门店
        $store = Store::findOne(["mch_id" => $mch->id]);
        if(!$store || $store->is_delete){
            throw new \Exception("无法获取门店信息");
        }

        //检查权限
        static::checkAuth($this->clerk_user_id, $mch);

        //核销订单
        static::clerkOrder($this->clerk_user_id, $store, $order);
    }
}