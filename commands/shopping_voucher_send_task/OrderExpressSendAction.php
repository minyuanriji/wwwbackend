<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class OrderExpressSendAction extends Action{

    const START_TIME = "2021-11-16 00:00:00";

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " OrderExpressSendAction start");
        while (true){
            try {
                if(!$this->newAction()){
                    $this->invalidClean();
                    $this->sendAction();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

    /**
     * 清理无效数据
     * @return void
     */
    private function invalidClean(){
        $query = ShoppingVoucherSendLog::find()->alias("l")->limit(10)->asArray();
        $query->innerJoin(["o" => Order::tableName()], "o.id=l.source_id");
        $query->where([
            "l.status"      => "waiting",
            "l.source_type" => "from_order_express"
        ]);
        $query->andWhere([
            "OR",
            ["o.is_pay" => 0],
            ["o.is_delete" => 1],
            ["o.is_recycle" => 1],
            ["NOT IN", "o.status", [1,2,3,6,7,8]]
        ]);
        $invalidIds = $query->select(["l.id"])->column();
        while($invalidIds){
            ShoppingVoucherSendLog::updateAll(["status" => "invalid"], "id IN(".implode(",", $invalidIds).")");
            $invalidIds = $query->select(["l.id"])->column();
        }
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){
        $query = ShoppingVoucherSendLog::find()->alias("l")->limit(10)->asArray();
        $query->innerJoin(["o" => Order::tableName()], "o.id=l.source_id");
        $query->where([
            "l.status"        => "waiting",
            "l.source_type"   => "from_order_express",
            "o.is_pay"        => 1,
            "o.cancel_status" => 0,
            "o.is_delete"     => 0,
            "o.is_recycle"    => 0,
            "o.is_confirm"    => 1
        ]);
        $query->andWhere([
            "AND",
            ["IN", "o.status", [1,2,3,6,7,8]]
        ]);
        $sendLogs = $query->select(["l.id", "l.user_id", "l.source_id", "l.source_type", "l.money"])
            ->orderBy("l.updated_at ASC")
            ->all();
        $sendLogIds = [];
        if($sendLogs){
            foreach($sendLogs as $sendLog){
                try {
                    $user = User::findOne($sendLog['user_id']);
                    if(!$user || $user->is_delete){
                        throw new \Exception("用户不存在");
                    }
                    $modifyForm = new ShoppingVoucherLogModifiyForm([
                        "money"       => $sendLog['money'],
                        "desc"        => "商品订单消费获得赠送红包",
                        "source_id"   => $sendLog['source_id'],
                        "source_type" => $sendLog['source_type']
                    ]);
                    $modifyForm->add($user, true);
                    $sendLogIds[] = $sendLog['id'];
                    $this->controller->commandOut("红包发放记录ID:" . $sendLog['id'] . "处理完成");
                }catch (\Exception $e){
                    $remark = implode("\n", [$e->getMessage(), "line:" . $e->getLine(), "file:".$e->getFile()]);
                    ShoppingVoucherSendLog::updateAll([
                        "status" => "invalid",
                        "remark" => $remark
                    ], ["id" => $sendLog['id']]);
                    $this->controller->commandOut($remark);
                }
            }
            if($sendLogIds){
                ShoppingVoucherSendLog::updateAll(["status" => "success"], "id IN (".implode(",", $sendLogIds).")");
            }
        }
    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){

        $query = Order::find()->alias("o");
        $query->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=o.id AND svsl.source_type='from_order_express'");
        $query->where([
            "o.is_pay"        => 1,
            "o.cancel_status" => 0,
            "o.is_delete"     => 0,
            "o.is_recycle"    => 0
        ]);
        $query->andWhere([
            "AND",
            ["IN", "o.status", [1,2,3,6,7,8]],
            ["o.enable_express_got_shopping_voucher" => 1],
            "o.express_price > 0",
            "o.created_at>" . strtotime(self::START_TIME),
            "svsl.id IS NULL"
        ]);
        $query->orderBy("o.updated_at ASC");

        $selects = ["o.id", "o.mall_id", "o.user_id", "o.express_price"];
        $orderArr = $query->select($selects)->asArray()->limit(10)->all();
        if(!$orderArr)
            return false;

        $orderIds = [];
        foreach($orderArr as $order){
            $orderIds[] = $order['id'];
        }
        Order::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderIds).")");

        foreach($orderArr as $order){
            $order['give_type'] = 1;
            $order['give_value'] = 100;
            $money = $order['express_price'] * (floatval($order['give_value'])/100);
            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $order['mall_id'],
                "user_id"     => $order['user_id'],
                "money"       => $money,
                "source_id"   => $order['id'],
                "source_type" => "from_order_express",
                "status"      => "waiting",
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($order)
            ]);
            if($sendLog->save()){
                $this->controller->commandOut("红包发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }
    }
}