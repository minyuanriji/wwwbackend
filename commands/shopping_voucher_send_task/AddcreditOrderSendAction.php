<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromAddcredit;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class AddcreditOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " AddcreditOrderSendAction start");
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
        $query = ShoppingVoucherSendLog::find()->alias("l")->limit(10)->asArray()->where(["l.status" => "waiting", "l.source_type" => "from_hotel_order"]);
        $query->innerJoin(["ho" => HotelOrder::tableName()], "ho.id=l.source_id");
        $query->andWhere("(UNIX_TIMESTAMP(ho.booking_start_date) + ho.booking_days * 3600 * 24) < '".time()."'");

        //把非入驻成功状态的订单全部取消掉
        $cloneQuery = clone $query;
        $cloneQuery->andWhere([
            "OR",
            "ho.pay_status <> 'paid'",
            "ho.order_status NOT IN('success', 'unconfirmed', 'finished')"
        ]);
        $invalidIds = $cloneQuery->select(["l.id"])->column();
        while($invalidIds){
            ShoppingVoucherSendLog::updateAll(["status" => "invalid"], "id IN(".implode(",", $invalidIds).")");
            $invalidIds = $cloneQuery->select(["l.id"])->column();
        }

        //取出正常记录进行发放
        $cloneQuery = clone $query;
        $cloneQuery->andWhere([
            "AND",
            "ho.pay_status='paid'",
            "ho.order_status IN('success', 'unconfirmed', 'finished')"
        ]);
        $sendLogs = $query->select(["l.id", "l.user_id", "l.source_id", "l.source_type", "l.money"])
            ->orderBy("l.updated_at ASC")
            ->all();
        $sendLogIds = [];
        foreach($sendLogs as $sendLog){
            try {
                $user = User::findOne($sendLog['user_id']);
                if(!$user || $user->is_delete){
                    throw new \Exception("用户不存在");
                }
                $modifyForm = new ShoppingVoucherLogModifiyForm([
                    "money"       => $sendLog['money'],
                    "desc"        => "酒店预订消费获得赠送购物券",
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
        $query = AddcreditOrder::find()->alias("ao");
        $query->leftJoin(["apf" => AddcreditPlateforms::tableName()], "ao.plateform_id=apf.id");
        $query->innerJoin(["svfa" => ShoppingVoucherFromAddcredit::tableName()], "(svfa.sdk_key=apf.sdk_dir) AND svfa.is_delete=0");
        $query->leftJoin(["svfa" => ShoppingVoucherSendLog::tableName()], "svfa.source_id=ao.id AND svfa.source_type='from_addcredit_order'");

        $query->andWhere([
            "AND",
            "ao.pay_price > 0",
            ["ao.pay_status" => 'paid']
        ]);
        $query->orderBy("ao.updated_at ASC");

        $selects = ["ao.id", "ao.mall_id", "ao.user_id", "ao.integral_deduction_price", "svfa.param_data_json"];

        $AddcreditOrder = $query->select($selects)->asArray()->limit(10)->all();
        if(!$AddcreditOrder)
            return false;

        $AddcreditOrderIds = [];
        foreach($AddcreditOrder as $item){
            $AddcreditOrderIds[] = $item['id'];
        }
        AddcreditOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $AddcreditOrderIds).")");

        foreach($AddcreditOrder as $value){

            $money = $hotelOrder['pay_price'] * (floatval($hotelOrder['give_value'])/100);

            $sendLog = new ShoppingVoucherSendLog([
                "mall_id"     => $hotelOrder['mall_id'],
                "user_id"     => $hotelOrder['user_id'],
                "source_id"   => $hotelOrder['id'],
                "source_type" => "from_hotel_order",
                "status"      => "waiting",
                "money"       => $money,
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($hotelOrder)
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