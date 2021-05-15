<?php
namespace app\commands;


use app\events\OrderEvent;
use app\models\Mall;
use app\models\Order;

class OrderAutoSaleController extends BaseCommandController {

    /**
     * 订单自动收货
     */
    public function actionMaintantJob(){

        $this->mutiKill(); //只能只有一个维护服务

        echo date("Y-m-d H:i:s") . " 订单自动结束守护程序启动...完成\n";

        $orderAutoSaleTime = 10 * 24 * 3600;

        while (true){
            $this->sleep(1);

            $offset = time() - $orderAutoSaleTime;
            $query = Order::find()->where([
                "is_pay"      => 1,
                "is_send"     => 1,
                "is_delete"   => 0,
                "is_recycle"  => 0,
                "sale_status" => 0,
                "is_confirm"  => Order::IS_CONFIRM_NO
            ])->andWhere(["IN", "status", [Order::STATUS_WAIT_RECEIVE]]);
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