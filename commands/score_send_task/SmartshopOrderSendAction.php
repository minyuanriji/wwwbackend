<?php

namespace app\commands\score_send_task;

use app\models\Integral;
use app\models\Mall;
use app\models\Store;
use app\models\User;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\integral_card\models\ScoreSendLog;
use app\plugins\mch\models\Mch;
use app\plugins\smart_shop\models\Order;
use yii\base\Action;

class SmartshopOrderSendAction extends Action{

    private $sleep = 1;

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " SmartshopOrderSendAction start");
        while (true){
            try {
                $this->newAction();
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(min(max($this->sleep, 1), 30));
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
            ->innerJoin(["sfs" => ScoreFromStore::tableName()], "sfs.store_id=s.id AND sfs.is_delete=0")
            ->leftJoin(["ssl" => ScoreSendLog::tableName()], "ssl.source_id=o.id AND ssl.source_type='from_smart_shop_order'");

        $query->andWhere([
            "AND",
            "ssl.id IS NULL",
            "o.created_at > sfs.start_at",
            "o.pay_price > 0",
            "u.mobile IS NOT NULL AND u.mobile <> ''",
            ["o.status" => Order::STATUS_FINISHED],
            ["o.is_delete" => 0],
            ["sfs.enable_score" => 1]
        ]);
        $query->orderBy("o.updated_at ASC");

        $selects = ["o.id", "o.mall_id", "u.id as pay_user_id", "o.pay_price", "m.id as mch_id", "s.id as store_id", "sfs.rate", "sfs.score_setting"];

        $orders = $query->select($selects)->asArray()->limit(10)->all();

        if(!$orders){
            $this->sleep = min(30, $this->sleep + 1);
            return false;
        }

        $this->sleep = max(1, $this->sleep - 1);

        $orderIds = [];
        foreach($orders as $order){
            $orderIds[] = $order['id'];
        }

        Order::updateAll(["updated_at" => time()], "id IN (".implode(",", $orderIds).")");

        foreach($orders as $order){

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

            if($sendLog->save()){
                \Yii::$app->mall = Mall::findOne($order['mall_id']);
                $scoreSetting = @json_decode($order['score_setting'], true);
                $scoreSetting['integral_num'] = floatval($order['pay_price'] * ($order['rate']/100));
                $scoreSetting['source_type']  = 'from_smart_shop_order';
                $scoreSetting['source_id']    = $order['id'];
                $res = Integral::addIntegralPlan($order['pay_user_id'], $scoreSetting, '智慧门店消费赠送积分券', '0');
                $this->controller->commandOut("积分发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }
}