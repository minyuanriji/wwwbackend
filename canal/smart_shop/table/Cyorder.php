<?php
namespace app\canal\smart_shop\table;


use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidEmailJob;
use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidMobileJob;
use app\plugins\smart_shop\components\cyorder_paid_notification\NotificationCyorderPaidWechatJob;
use app\plugins\smart_shop\components\cyorder_refund_notification\NotificationCyorderRefundEmailJob;
use app\plugins\smart_shop\components\cyorder_refund_notification\NotificationCyorderRefundMobileJob;
use app\plugins\smart_shop\components\cyorder_refund_notification\NotificationCyorderRefundWechatJob;

class Cyorder{

    public function insert($rows){}

    public function update($mixDatas){
        foreach ($mixDatas as $mixData) {
            $condition = $mixData['condition'];
            $updates = $mixData['update'];

            //订单付款，通知商户
            if(isset($updates['is_pay']) && $updates['is_pay'] == 1){
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

            //取消订单，申请退款
            if(isset($updates['apply_refund']) && isset($updates['cancel_status'])
                && $updates['apply_refund'] == 1 && $updates['cancel_status'] == 1){
                \Yii::$app->queue->delay(1)->push(new NotificationCyorderRefundWechatJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(2)->push(new NotificationCyorderRefundMobileJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
                \Yii::$app->queue->delay(3)->push(new NotificationCyorderRefundEmailJob([
                    "mall_id"  => \Yii::$app->mall->id,
                    "order_id" => $condition['id']
                ]));
            }

        }
    }

}