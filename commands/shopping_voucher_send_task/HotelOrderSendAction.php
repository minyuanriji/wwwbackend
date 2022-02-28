<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromHotel;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class HotelOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " HotelOrderSendAction start");
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
        $sendLogs = $cloneQuery->select(["l.id", "l.user_id", "l.source_id", "l.source_type", "l.money"])
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
                    "desc"        => "酒店预订消费获得赠送红包",
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

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = HotelOrder::find()->alias("ho");
        $query->innerJoin(["svfh" => ShoppingVoucherFromHotel::tableName()], "(svfh.hotel_id=ho.hotel_id OR svfh.hotel_id=0) AND svfh.is_delete=0");
        $query->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=ho.id AND svsl.source_type='from_hotel_order'");

        $query->andWhere([
            "AND",
            "svsl.id IS NULL",
            "ho.created_at>svfh.start_at",
            "ho.pay_price > 0",
            ["ho.pay_status" => 'paid']
        ]);
        $query->orderBy("ho.updated_at ASC");

        $selects = ["ho.id", "ho.mall_id", "ho.user_id", "ho.pay_price", "ho.hotel_id", "svfh.give_type", "svfh.give_value"];

        $hotelOrders = $query->select($selects)->asArray()->limit(10)->all();
        if(!$hotelOrders)
            return false;

        $hotelOrderIds = [];
        foreach($hotelOrders as $hotelOrder){
            $hotelOrderIds[] = $hotelOrder['id'];
        }
        HotelOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $hotelOrderIds).")");

        foreach($hotelOrders as $hotelOrder){

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
                $this->controller->commandOut("红包发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }
}