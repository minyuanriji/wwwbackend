<?php
namespace app\notification;

use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\OrderRefundSuccessNotificationWeTplJob;
use app\notification\wechat_template_message\OrderRefundSuccessNotificationWeTplMsg;

class OrderRefundSuccessNotification
{
    public static function send(OrderRefund $order_refund)
    {
        (new OrderRefundSuccessNotificationWeTplJob([
            "order_refund" => $order_refund
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new OrderRefundSuccessNotificationWeTplJob([
            "order_refund" => $order_refund
        ]));*/
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

        (new OrderRefundSuccessNotificationWeTplMsg([
            "mall_id"           => $order_refund->mall_id,
            "openid"            => $userInfo->openid,
            "price"             => $order_refund->reality_refund_price . '+红包（' . $order->integral_deduction_price . '）',
            "goods_name"        => $goods->goodsWarehouse->name,
            "order_no"          => $order_refund->order_no,
            "remark"            => ($order_refund->type == 1 || $order_refund->type == 2)
                                    ?
                                    '为了保证您的利益，请您尽快发货给卖家,如有疑问请联系020-31923526!'
                                    :
                                    '如有疑问请联系020-31923526',
        ]))->send();
    }
}