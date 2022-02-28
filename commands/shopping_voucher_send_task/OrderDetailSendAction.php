<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class OrderDetailSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " OrderDetailSendAction start");
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
        $query->innerJoin(["od" => OrderDetail::tableName()], "od.id=l.source_id AND l.source_type='from_order_detail'");
        $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->where([
            "l.status"      => "waiting",
            "l.source_type" => "from_order_detail"
        ]);
        $query->andWhere([
            "OR",
            ["o.is_pay" => 0],
            ["o.is_delete" => 1],
            ["o.is_recycle" => 1],
            ["od.is_delete" => 1],
            ["od.is_refund" => 1],
            ["NOT IN", "o.status", [1,2,3,6,7,8]],
            ["NOT IN", "od.refund_status", [0, 21]],
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
        $query->innerJoin(["od" => OrderDetail::tableName()], "od.id=l.source_id");
        $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        $query->where([
            "l.status"        => "waiting",
            "l.source_type"   => "from_order_detail",
            "o.is_pay"        => 1,
            "o.cancel_status" => 0,
            "o.is_delete"     => 0,
            "o.is_recycle"    => 0,
            "o.is_confirm"    => 1,
            "od.is_delete"    => 0,
            "od.is_refund"    => 0
        ]);
        $query->andWhere([
            "AND",
            ["IN", "o.status", [1,2,3,6,7,8]],
            ["IN", "od.refund_status", [0, 21]]
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
        $query = OrderDetail::find()->alias("od");
        $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
        //$query->innerJoin(["svfg" => ShoppingVoucherFromGoods::tableName()], "(svfg.goods_id=od.goods_id OR svfg.goods_id=0) AND svfg.is_delete=0");
        $query->innerJoin(["svfg" => ShoppingVoucherFromGoods::tableName()], "(svfg.goods_id=od.goods_id) AND svfg.is_delete=0");
        $query->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=od.id AND svsl.source_type='from_order_detail'");
        $query->where([
            "o.is_pay"        => 1,
            "o.cancel_status" => 0,
            "o.is_delete"     => 0,
            "o.is_recycle"    => 0,
            "o.is_confirm"    => 0,
            "od.is_delete"    => 0,
            "od.is_refund"    => 0
        ]);
        $query->andWhere([
            "AND",
            ["IN", "o.status", [1,2,3,6,7,8]],
            ["IN", "od.refund_status", [0, 10, 21]],
            "od.total_price > 0",
            "od.created_at>svfg.start_at",
            "svsl.id IS NULL"
        ]);
        $query->orderBy("od.updated_at ASC");

        $selects = ["od.id", "o.mall_id", "o.user_id", "od.total_price", "od.goods_id", "svfg.give_type", "svfg.give_value"];
        $orderDetails = $query->select($selects)->asArray()->limit(10)->all();
        if(!$orderDetails)
            return false;

        $orderDetailIds = [];
        foreach($orderDetails as $orderDetail){
            $orderDetailIds[] = $orderDetail['id'];
        }
        OrderDetail::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderDetailIds).")");

        foreach($orderDetails as $orderDetail){
            $money = $orderDetail['total_price'] * (floatval($orderDetail['give_value'])/100);
            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $orderDetail['mall_id'],
                "user_id"     => $orderDetail['user_id'],
                "money"       => $money,
                "source_id"   => $orderDetail['id'],
                "source_type" => "from_order_detail",
                "status"      => "waiting",
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($orderDetail)
            ]);
            if($sendLog->save()){
                $this->controller->commandOut("红包发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }
    }
}