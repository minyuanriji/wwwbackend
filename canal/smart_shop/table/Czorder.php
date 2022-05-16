<?php
namespace app\canal\smart_shop\table;



use app\plugins\smart_shop\components\czorder_paid_notification\NotificationCzorderPaidEmailJob;
use app\plugins\smart_shop\components\czorder_paid_notification\NotificationCzorderPaidMobileJob;
use app\plugins\smart_shop\components\czorder_paid_notification\NotificationCzorderPaidWechatJob;

class Czorder{

    public function insert($rows){}

    public function update($mixDatas){
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $updates = $mixData['update'];

            echo json_encode($mixData) . "\n";

            //订单付款，通知商户
            if(isset($updates['state']) && $updates['state'] == 2){
                \Yii::$app->queue->delay(1)->push(new NotificationCzorderPaidWechatJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(2)->push(new NotificationCzorderPaidMobileJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(3)->push(new NotificationCzorderPaidEmailJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
            }


        }
    }

}