<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromStore;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class MchCheckoutOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " MchCheckoutOrderSendAction start");
        while (true){
            try {
                if(!$this->newAction()){
                    $this->sendAction();
                }
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }

    /**
     * 处理发放记录
     * @return void
     */
    private function sendAction(){
        $sendLogs = ShoppingVoucherSendLog::find()->where(["status" => "waiting"])
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
                    "desc"        => "门店消费获得赠送购物券",
                    "source_id"   => $sendLog['source_id'],
                    "source_type" => $sendLog['source_type']
                ]);
                $modifyForm->add($user, true);
                $sendLogIds[] = $sendLog['id'];
                $this->controller->commandOut("购物券发放记录ID:" . $sendLog['id'] . "处理完成");
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

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = MchCheckoutOrder::find()->alias("mco");
        $query->innerJoin(["m" => Mch::tableName()], "m.id=mco.mch_id AND m.is_delete=0 AND m.review_status=1");
        $query->innerJoin(["svfs" => ShoppingVoucherFromStore::tableName()], "svfs.store_id=mco.store_id AND svfs.is_delete=0");
        $query->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=mco.id AND svsl.source_type='from_mch_checkout_order'");
        $query->andWhere([
            "AND",
            "svsl.id IS NULL",
            [">", "mco.created_at", 0],
            "mco.pay_price > 0",
            ["mco.is_pay" => 1],
            ["mco.is_delete" => 0]
        ]);
        $query->orderBy("mco.updated_at ASC");

        $selects = ["mco.id", "mco.mall_id", "mco.pay_user_id", "mco.pay_price", "mco.mch_id", "mco.store_id", "svfs.give_type", "svfs.give_value"];
        $checkOrders = $query->select($selects)->asArray()->limit(10)->all();

        if(!$checkOrders)
            return false;

        $checkOrderIds = [];
        foreach($checkOrders as $checkOrder){
            $checkOrderIds[] = $checkOrder['id'];
        }
        MchCheckoutOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $checkOrderIds).")");

        foreach($checkOrders as $checkOrder){

            $money = $checkOrder['pay_price'] * (floatval($checkOrder['give_value'])/100);

            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $checkOrder['mall_id'],
                "user_id"     => $checkOrder['pay_user_id'],
                "source_id"   => $checkOrder['id'],
                "source_type" => "from_mch_checkout_order",
                "status"      => "waiting",
                "money"       => $money,
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($checkOrder)
            ]);

            if($sendLog->save()){
                $this->controller->commandOut("购物券发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }
}