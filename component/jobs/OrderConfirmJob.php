<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单确认任务类
 * Author: zal
 * Date: 2020-04-10
 * Time: 19:16
 */

namespace app\component\jobs;

use app\events\OrderEvent;
use app\models\Mall;
use app\models\Order;
use yii\base\Component;
use yii\queue\JobInterface;

class OrderConfirmJob extends Component implements JobInterface
{
    public $orderId;

    public function execute($queue)
    {
        \Yii::error('order confirm job ->>' . $this->orderId);
        /** @var Order $order */
        $order = Order::getOrderInfo([
            'id' => $this->orderId,
            'is_delete' => 0,
            'is_send' => 1,
            'is_confirm' => 0
        ]);
        if (!$order) {
            return true;
        }
        $mall = Mall::findOne(['id' => $order->mall_id]);
        \Yii::$app->setMall($mall);
        if ($order->pay_type == Order::PAY_TYPE_GOODS_PAY) {
            \Yii::error('货到付款的无法自动收货');
            return true;
        }

        // TODO 订单处于售后状态是未处理
        $order->is_confirm = Order::IS_CONFIRM_YES;
        $order->confirm_at = time();
        if ($order->save()) {
            $event = new OrderEvent([
                'order' => $order,
            ]);
            \Yii::$app->trigger(Order::EVENT_CONFIRMED, $event);
        }
    }
}
