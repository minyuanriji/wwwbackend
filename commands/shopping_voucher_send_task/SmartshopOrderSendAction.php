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

/**
 * @deprecated
 */
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
            ->innerJoin(["u" => User::tableName()], "u.mobile=o.pay_user_mobile");

        $query->andWhere([
            "AND",
            "o.created_at > svfs.start_at",
            "o.pay_price > 0",
            "u.mobile IS NOT NULL",
            "u.mobile <> ''",
            ["o.shopping_voucher_status" => 0],
            ["o.status" => Order::STATUS_FINISHED],
            ["o.is_delete" => 0]
        ]);
        $query->orderBy("o.updated_at ASC");

        $selects = ["o.id", "o.mall_id", "o.pay_price", "o.split_amount", "o.wx_got_amount", "o.ali_got_amount", "u.id as user_id", "s.mch_id", "s.id as store_id",
            "svfs.give_type", "svfs.give_value", "m.transfer_rate", "o.transfer_rate as transfer_rate2"];
        $orders = $query->select($selects)->asArray()->limit(1)->all();

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
            $this->processOrder($order);
        }

        return true;
    }

    /**
     * 处理新增发送记录
     * @param $order
     */
    private function processOrder($order){
        try {

            if(($order['wx_got_amount'] > $order['split_amount']) &&
                ($order['ali_got_amount'] > $order['split_amount'])){
                throw new \Exception("支付公司扣取手续费大于分账金额 ID:" . $order['id']);
            }

            $transferRate = $order['transfer_rate2'] ? $order['transfer_rate2'] : $order['transfer_rate'];
            $giveValue = ShoppingVoucherHelper::calculateMchRateByTransferRate($transferRate);

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

            if(!$sendLog->save()){
                throw new \Exception(json_encode($sendLog->getErrors()));
            }

            $this->controller->commandOut("红包发放记录创建成功，ID:" . $sendLog->id);
        }catch (\Exception $e){
            $this->controller->commandOut($e->getMessage());
        }
        Order::updateAll(["shopping_voucher_status" => 1], ["id" => $order['id']]);
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