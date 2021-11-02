<?php

namespace app\commands\shopping_voucher_send_task;

use app\models\User;
use app\plugins\giftpacks\models\GiftpacksOrder;
use app\plugins\shopping_voucher\forms\common\ShoppingVoucherLogModifiyForm;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGiftpacks;
use app\plugins\shopping_voucher\models\ShoppingVoucherSendLog;
use yii\base\Action;

class GiftpacksOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " GiftpacksOrderSendAction start");
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
        $query = ShoppingVoucherSendLog::find()->alias("l")->limit(10)->asArray()
                    ->where(["l.status" => "waiting", "l.source_type" => "from_giftpacks_order"]);
        $query->innerJoin(["go" => GiftpacksOrder::tableName()], "go.id=l.source_id");

        $query->andWhere([
            "AND",
            ["go.is_delete" => 0],
            ["go.pay_status" => "paid"]
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
                        "desc"        => "大礼包订单消费获得赠送购物券",
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
    }

    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = GiftpacksOrder::find()->alias("go");
        $query->leftJoin(["svsl" => ShoppingVoucherSendLog::tableName()], "svsl.source_id=go.id AND svsl.source_type='from_giftpacks_order'");
        $query->innerJoin(["u" => User::tableName()], "u.id=go.user_id");
        $query->leftJoin(["p" => User::tableName()], "p.id=u.parent_id");

        $query->andWhere([
            "AND",
            "svsl.id IS NULL",
            "go.created_at>svfg.start_at",
            "go.pay_price > 0",
            ["go.pay_status" => 'paid']
        ]);
        $query->orderBy("go.updated_at ASC");

        $selects = ["go.id", "go.mall_id", "go.user_id", "go.pay_price", "go.pack_id", "svfg.give_type", "svfg.give_value", "svfg.recommender",
            "u.parent_id", "p.role_type as parent_role_type"
        ];
        $query->select($selects)->asArray()->limit(10);

        //指定配置
        $cloneQuery = clone $query;
        $cloneQuery->innerJoin(["svfg" => ShoppingVoucherFromGiftpacks::tableName()], "svfg.pack_id=go.pack_id");
        $giftpacksOrders = $cloneQuery->all();

        //通用配置
        if(!$giftpacksOrders){
            $cloneQuery = clone $query;
            $cloneQuery->innerJoin(["svfg" => ShoppingVoucherFromGiftpacks::tableName()], "svfg.pack_id=0 AND svfg.is_delete=0");
            $giftpacksOrders = $cloneQuery->all();
        }

        if(!$giftpacksOrders)
            return false;

        $giftpacksOrderIds = [];
        foreach($giftpacksOrders as $giftpacksOrder){
            $giftpacksOrderIds[] = $giftpacksOrder['id'];
        }
        GiftpacksOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $giftpacksOrderIds).")");
        foreach($giftpacksOrders as $giftpacksOrder){
            if($giftpacksOrder['give_type'] == 2){ //固定值
                $money = floatval($giftpacksOrder['give_value']);
            }else{ //比例
                $money = $giftpacksOrder['pay_price'] * (floatval($giftpacksOrder['give_value'])/100);
            }

            $userDatas = [];
            $userDatas[] = ['user_id' => $giftpacksOrder['user_id'], 'money' => $money];

            //推荐人也有奖励
            $recommender = @json_decode($giftpacksOrder['recommender'], true);
            if($recommender && !empty($giftpacksOrder['parent_role_type'])){
                foreach($recommender as $recommenderItem){
                    if($giftpacksOrder['parent_role_type'] == $recommenderItem['type']){
                        if($recommenderItem['give_type'] == 2){ //固定值
                            $money = floatval($recommenderItem['give_value']);
                        }else{ //比例
                            $money = $giftpacksOrder['pay_price'] * (floatval($recommenderItem['give_value'])/100);
                        }
                        $userDatas[] = [
                            'user_id' => $giftpacksOrder['parent_id'],
                            'money'   => $money
                        ];
                        break;
                    }
                }
            }

            foreach($userDatas as $userData){
                $sendLog = new ShoppingVoucherSendLog([
                    "mall_id"     => $giftpacksOrder['mall_id'],
                    "user_id"     => $userData['user_id'],
                    "money"       => $userData['money'],
                    "source_id"   => $giftpacksOrder['id'],
                    "source_type" => "from_giftpacks_order",
                    "status"      => "waiting",
                    "created_at"  => time(),
                    "updated_at"  => time(),
                    "data_json"   => json_encode($giftpacksOrder)
                ]);

                if($sendLog->save()){
                    $this->controller->commandOut("购物券发放记录创建成功，ID:" . $sendLog->id);
                }else{
                    $this->controller->commandOut(json_encode($sendLog->getErrors()));
                }
            }
        }

        return true;
    }
}