<?php

namespace app\commands\score_send_task;

use app\commands\BaseAction;
use app\models\Integral;
use app\models\Mall;
use app\models\Store;
use app\models\User;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\integral_card\models\ScoreSendLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Order;

/**
 * @deprecated
 */
class SmartshopOrderSendAction extends BaseAction {

    public function run(){
        if(!\Yii::$app->getMallId()){
            \Yii::$app->setMall(Mall::findOne(5));
        }
        $this->controller->commandOut("SmartshopOrderSendAction start");
        while (true){
            sleep($this->sleepTime);
            try {
                $this->newAction();
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
            ->innerJoin(["u" => User::tableName()], "u.mobile=o.pay_user_mobile")
            ->innerJoin(["sfs" => ScoreFromStore::tableName()], "sfs.store_id=s.id AND sfs.is_delete=0");

        $query->andWhere([
            "AND",
            "o.created_at > sfs.start_at",
            "o.pay_price > 0",
            "u.mobile IS NOT NULL",
            "u.mobile <> ''",
            ["o.score_status" => 0],
            ["o.status" => Order::STATUS_FINISHED],
            ["o.is_delete" => 0],
            ["sfs.enable_score" => 1]
        ]);
        $query->orderBy("o.updated_at ASC");

        $selects = ["o.id", "o.mall_id", "u.id as pay_user_id", "o.pay_price", "m.id as mch_id", "s.id as store_id", "sfs.rate", "sfs.score_setting"];

        $orders = $query->select($selects)->asArray()->limit(1)->all();

        if(!$orders){
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
     * 处理订单的积分发放
     * @param $order
     */
    private function processOrder($order){
        try {
            $sendLog = new ScoreSendLog([
                "mall_id"     => $order['mall_id'],
                "user_id"     => $order['pay_user_id'],
                "source_id"   => $order['id'],
                "source_type" => "from_smart_shop_order",
                "status"      => "success",
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($order)
            ]);

            if(!$sendLog->save()){
                throw new \Exception(json_encode($sendLog->getErrors()));
            }

            $scoreSetting = @json_decode($order['score_setting'], true);
            $scoreSetting['integral_num'] = floatval($order['pay_price']);
            $scoreSetting['source_type']  = 'from_smart_shop_order';
            $scoreSetting['source_id']    = $order['id'];
            $res = Integral::addIntegralPlan($order['pay_user_id'], $scoreSetting, '智慧门店消费赠送积分券', '0');
            if(!$res){
                throw new \Exception(Integral::getError());
            }
            $this->controller->commandOut("积分发放记录创建成功，ID:" . $sendLog->id);
        }catch (\Exception $e){
            $this->controller->commandOut($e->getMessage());
        }

        Order::updateAll(["score_status" => 1], ["id" => $order['id']]);
    }
}