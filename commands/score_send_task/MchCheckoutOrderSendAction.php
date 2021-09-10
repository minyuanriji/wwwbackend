<?php

namespace app\commands\score_send_task;

use app\models\Integral;
use app\models\Mall;
use app\plugins\integral_card\models\ScoreFromStore;
use app\plugins\integral_card\models\ScoreSendLog;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCheckoutOrder;
use yii\base\Action;

class MchCheckoutOrderSendAction extends Action{

    public function run(){
        $this->controller->commandOut(date("Y/m/d H:i:s") . " MchCheckoutOrderSendAction start");
        while (true){
            try {
                $this->newAction();
            }catch (\Exception $e){
                $this->controller->commandOut(implode("\n", [$e->getMessage(), $e->getFile(), $e->getLine()]));
            }
            $this->controller->sleep(1);
        }
    }


    /**
     * 新增发送记录
     * @return bool
     */
    private function newAction(){
        $query = MchCheckoutOrder::find()->alias("mco");
        $query->innerJoin(["m" => Mch::tableName()], "m.id=mco.mch_id AND m.is_delete=0 AND m.review_status=1");
        $query->innerJoin(["sfs" => ScoreFromStore::tableName()], "sfs.store_id=mco.store_id AND sfs.is_delete=0");
        $query->leftJoin(["ssl" => ScoreSendLog::tableName()], "ssl.source_id=mco.id AND ssl.source_type='from_mch_checkout_order'");

        $query->andWhere([
            "AND",
            "ssl.id IS NULL",
            "mco.created_at>sfs.start_at",
            "mco.pay_price > 0",
            ["mco.is_pay" => 1],
            ["mco.is_delete" => 0],
            ["sfs.enable_score" => 1]
        ]);
        $query->orderBy("mco.updated_at ASC");

        $selects = ["mco.id", "mco.mall_id", "mco.pay_user_id", "mco.pay_price", "mco.mch_id", "mco.store_id", "sfs.score_setting"];

        $checkOrders = $query->select($selects)->asArray()->limit(10)->all();

        if(!$checkOrders)
            return false;

        $checkOrderIds = [];
        foreach($checkOrders as $checkOrder){
            $checkOrderIds[] = $checkOrder['id'];
        }
        MchCheckoutOrder::updateAll(["updated_at" => time()], "id IN (".implode(",", $checkOrderIds).")");

        foreach($checkOrders as $checkOrder){

            $sendLog = new ScoreSendLog([
                "mall_id"     => $checkOrder['mall_id'],
                "user_id"     => $checkOrder['pay_user_id'],
                "source_id"   => $checkOrder['id'],
                "source_type" => "from_mch_checkout_order",
                "status"      => "success",
                "created_at"  => time(),
                "updated_at"  => time(),
                "data_json"   => json_encode($checkOrder)
            ]);

            if($sendLog->save()){
                \Yii::$app->mall = Mall::findOne($checkOrder['mall_id']);
                $scoreSetting = @json_decode($checkOrder['score_setting'], true);
                $res = Integral::addIntegralPlan($checkOrder['pay_user_id'], $scoreSetting, '商家二维码支付赠送积分券', '0');
                $this->controller->commandOut("积分发放记录创建成功，ID:" . $sendLog->id);
            }else{
                $this->controller->commandOut(json_encode($sendLog->getErrors()));
            }
        }

        return true;
    }
}