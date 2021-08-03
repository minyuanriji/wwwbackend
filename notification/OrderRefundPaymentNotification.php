<?php
namespace app\notification;

use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\OrderRefundPaymentNotificationWeTplJob;
use app\notification\wechat_template_message\OrderRefundPaymentNotificationWeTplMsg;

class OrderRefundPaymentNotification
{

    public static function send(OrderRefund $order_refund)
    {
        /*(new OrderRefundPaymentNotificationWeTplJob([
            "order_refund" => $order_refund
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new OrderRefundPaymentNotificationWeTplJob([
            "order_refund" => $order_refund
        ]));
    }

    public static function sendWechatTemplate(OrderRefund $order_refund)
    {
        $user = User::findOne($order_refund->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $order = Order::findOne($order_refund->order_id);
        if(!$order) return;

        $order_detail = OrderDetail::findOne($order_refund->order_detail_id);
        if(!$order_detail) return;

        $goods = Goods::find()->with('goodsWarehouse')->where(['id' => $order_detail->goods_id])->one();
        if(!$goods) return;

        (new OrderRefundPaymentNotificationWeTplMsg([
            "mall_id"           => $order_refund->mall_id,
            "openid"            => $userInfo->openid,
            "first"             => '您订单号为'. $order->order_no .'的已退款成功',
            "price"             => $order_refund->reality_refund_price . '+红包（' . $order->integral_deduction_price . '）',
        ]))->send();
    }
}