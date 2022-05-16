<?php
namespace app\canal\smart_shop\table;



use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidEmailJob;
use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidMobileJob;
use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidWechatJob;

class Czorder{

    public function insert($rows){}

    public function update($mixDatas){
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $updates = $mixData['update'];

            echo json_encode($mixData) . "\n";

            //订单付款，通知商户
            if(isset($updates['state']) && $updates['state'] == 2){
                \Yii::$app->queue->delay(1)->push(new NotificationCyorderPaidWechatJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(2)->push(new NotificationCyorderPaidMobileJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(3)->push(new NotificationCyorderPaidEmailJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
            }


        }
    }

}