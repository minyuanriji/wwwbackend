<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

namespace app\forms\api\user;

use app\core\payment\PaymentNotify;
use app\models\RechargeOrders;
use app\models\User;

class UserRechargePayNotify extends PaymentNotify
{
    public function notify($paymentOrder)
    {
        try {
            /* @var RechargeOrders $order */
            $order = RechargeOrders::find()->where(['order_no' => $paymentOrder->orderNo])->one();

            if (!$order) {
                throw new \Exception('订单不存在:' . $paymentOrder->orderNo);
            }

            if ($order->pay_type != 1) {
                throw new \Exception('必须使用微信支付');
            }

            $order->is_pay = 1;
            $order->pay_at = time();
            $res = $order->save();

            if (!$res) {
                throw new \Exception('充值订单支付状态更新失败');
            }

            $user = User::findOne($order->user_id);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            $price = (float)($order->pay_price + $order->give_money);
            $desc = '充值余额：' . $order->pay_price . '元,赠送：' . $order->give_money . '元';
            $customDesc = \Yii::$app->serializer->encode($order->attributes);
            \Yii::$app->currency->setUser($user)->balance->add($price, $desc, $customDesc);
            if($order->give_score > 0){
                \Yii::$app->currency->setUser($user)->score->add(
                    $order->give_score,
                    "余额充值,赠送积分{$order->give_score}",
                    $customDesc
                );
            }

        } catch (\Exception $e) {
            \Yii::error($e);
            throw $e;
        }
    }
}
