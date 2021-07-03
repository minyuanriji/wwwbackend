<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 取消订单任务类
 * Author: zal
 * Date: 2020-04-10
 * Time: 19:16
 */

namespace app\component\jobs;

use app\events\OrderEvent;
use app\models\BaseModel;
use app\models\Mall;
use app\models\Order;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class OrderCancelJob extends BaseObject implements JobInterface
{
    public $orderId;

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $order = Order::findOne([
            'id' => $this->orderId,
            'is_pay' => 0,
            'pay_type' => 0,
            'is_delete' => 0,
        ]);
        if (!$order) {
            return;
        }
        if ($order->cancel_status == 1) {
            return ;
        }
        $mall = Mall::findOne(['id' => $order->mall_id]);
        \Yii::$app->setMall($mall);
        $t = \Yii::$app->db->beginTransaction();
        try {
            $order->status = Order::STATUS_CLOSE;
            $order->cancel_status = 1;
            $order->cancel_at = time();
            if ($order->save()) {
                $event = new OrderEvent([
                    'order' => $order,
                ]);
                \Yii::$app->trigger(Order::EVENT_CANCELED, $event);
                $t->commit();
            } else {
                throw new \Exception((new BaseModel())->responseErrorMsg($order));
            }
        } catch (\Exception $exception) {
            $t->rollBack();
        }
    }
}
