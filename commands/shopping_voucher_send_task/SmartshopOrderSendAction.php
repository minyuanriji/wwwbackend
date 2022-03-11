<?php

namespace app\commands\shopping_voucher_send_task;

use app\commands\BaseAction;
use app\models\Store;
use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\helpers\ShoppingVoucherHelper;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use app\plugins\smart_shop\models\Order;

class SmartshopOrderSendAction extends BaseAction {

    public function run(){
        while (true){
            sleep($this->sleepTime);
            try {
                if(!$this->newAction()){
                    $this->sendAction();
                }
            }catch (\Exception $e){
                throw $e;
            }
        }
    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = Order::find()->alias("o")
            ->innerJoin(["m" => Mch::tableName()], "m.id=o.bsh_mch_id AND m.is_delete=0 AND m.review_status=1")
            ->innerJoin(["s" => Store::tableName()], "s.mch_id=o.bsh_mch_id")
            ->innerJoin(["svfs" => ShoppingVoucherFromStore::tableName()], "svfs.store_id=s.id AND svfs.is_delete=0")
            ->innerJoin(["u" => User::tableName()], "u.mobile=o.pay_user_mobile")
            ->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=o.id AND svsl.source_type='from_smart_shop_order'");

        $query->andWhere([
            "AND",
            "svsl.id IS NULL",
            "o.created_at > svfs.start_at",
            "o.pay_price > 0",
            "u.mobile IS NOT NULL AND u.mobile <> ''",
            ["o.status" => Order::STATUS_FINISHED],
            ["o.is_delete" => 0]
        ]);
        $query->orderBy("o.updated_at ASC");

        $selects = ["o.id", "o.mall_id", "o.pay_price", "u.id as user_id", "s.mch_id", "s.id as store_id",
            "svfs.give_type", "svfs.give_value", "m.transfer_rate"];
        $orders = $query->select($selects)->asArray()->limit(10)->all();

        if(!$orders) {
            $this->negativeTime();
            return false;
        }

        $this->activeTime();

        $orderIds = [];
        foreach($orders as $order){
            $orderIds[] = $order['id'];
        }
        Order::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderIds).")");

        foreach($orders as $order){

            $giveValue = ShoppingVoucherHelper::calculateMchRateByTransferRate($order['transfer_rate']);
            $order['give_value'] = $giveValue;
            $money = $order['pay_price'] * (floatval($giveValue)/100);

            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $order['mall_id'],
                "user_id"     => $order['user_id'],
                "source_id"   => $order['id'],
                "source_type" => "from_smart_shop_order",
                "status"      => "waiting",
                "money"       => $money,
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

        return true;
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){
        $sendLogs = ShoppingVoucherSendLog::find()->where(["status" => "waiting", "source_type" => "from_smart_shop_order"])
            ->select(["id", "user_id", "source_id", "source_type", "money"])
            ->orderBy("updated_at ASC")
            ->asArray()->limit(10)->all();
        $sendLogIds = [];
        foreach($sendLogs as $sendLog){
            try {
                $user = User::findOne($sendLog['user_id']);
                if(!$user || $user->is_delete){
                    throw new \Exception("用户不存在");
                }
                $modifyForm = new ShoppingVoucherLogModifiyForm([
                    "money"       => $sendLog['money'],
                    "desc"        => "门店消费获得赠送红包",
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
            $this->activeTime();
        }else{
            $this->negativeTime();
        }
    }
}