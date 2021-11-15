<?php
namespace app\commands;


use app\events\OrderEvent;
use app\logic\IntegralLogic;
use app\models\Mall;
use app\models\Order;

class OrderAutoSaleController extends BaseCommandController {

    /**
     * 订单自动收货
     */
    public function actionMaintantJob(){

        $this->mutiKill(); //只能只有一个维护服务

        echo date("Y-m-d H:i:s") . " 订单自动结束守护程序启动...完成\n";

        $orderAutoSaleTime = 7 * 24 * 3600;//无延长

        $orderExtendedAutoSaleTime = 14 * 24 * 3600;//延长一次

        while (true){
            $this->sleep(1);

            $query = Order::find()->where([
                "is_pay"      => 1,
                "is_send"     => 1,
                "is_delete"   => 0,
                "is_recycle"  => 0
            ])->andWhere([
                'and',
                ["IN", "sale_status", [0, 2]],
                ['status' => Order::STATUS_WAIT_RECEIVE],
                "pay_type <> '".Order::PAY_TYPE_GOODS_PAY."'"
            ]);

            $order = $query->orderBy("id ASC")->one();
            if(!$order) continue;

            if ($order->expand_num == 1) {
                $offset = time() - $orderExtendedAutoSaleTime;
            } else {
                $offset = time() - $orderAutoSaleTime;
            }

            if ($order->created_at <= $offset) {
                $mall = Mall::findOne(['id' => $order->mall_id]);
                \Yii::$app->setMall($mall);

                $order->auto_confirm_at = time();
                $order->confirm_at      = time();
                $order->is_confirm      = Order::IS_CONFIRM_YES;
                $order->status          = Order::STATUS_WAIT_COMMENT;
                try {
                    if (!$order->save()) {
                        throw new \Exception(json_encode($order->getErrors()));
                    }

                    //赠送积分
                    IntegralLogic::sendScore($order);

                    $event = new OrderEvent([
                        'order' => $order,
                    ]);
                    \Yii::$app->trigger(Order::EVENT_CONFIRMED, $event);
                    $this->commandOut("order[ID:".$order->id."] auto confirm success");
                }catch (\Exception $e){
                    $this->commandOut($e->getMessage());
                }
            }
        }
    }

    public function actionMaintantJobOld(){

        $this->mutiKill(); //只能只有一个维护服务

        echo date("Y-m-d H:i:s") . " 订单自动结束守护程序启动...完成\n";

        $orderAutoSaleTime = 7 * 24 * 3600;//无延长

        while (true){
            $this->sleep(1);

            $offset = time() - $orderAutoSaleTime;
            $query = Order::find()->where([
                "is_pay"      => 1,
                "is_send"     => 1,
                "is_delete"   => 0,
                "is_recycle"  => 0
            ])->andWhere(["IN", "sale_status", [0, 2]])->andWhere(["IN", "status", [Order::STATUS_WAIT_RECEIVE]]);
            $query->andWhere("created_at <= '{$offset}'");
            $query->andWhere("pay_type <> '".Order::PAY_TYPE_GOODS_PAY."'");

            $order = $query->orderBy("id ASC")->one();
            if(!$order) continue;

            $mall = Mall::findOne(['id' => $order->mall_id]);
            \Yii::$app->setMall($mall);

            $order->auto_confirm_at = time();
            $order->confirm_at      = time();
            $order->is_confirm      = Order::IS_CONFIRM_YES;
            $order->status          = Order::STATUS_WAIT_COMMENT;
            try {
                if (!$order->save()) {
                    throw new \Exception(json_encode($order->getErrors()));
                }

                //赠送积分
                IntegralLogic::sendScore($order);

                $event = new OrderEvent([
                    'order' => $order,
                ]);
                \Yii::$app->trigger(Order::EVENT_CONFIRMED, $event);
                $this->commandOut("order[ID:".$order->id."] auto confirm success");
            }catch (\Exception $e){
                $this->commandOut($e->getMessage());
            }
        }

    }
}