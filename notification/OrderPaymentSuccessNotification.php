<?php
namespace app\notification;

use app\models\EfpsPaymentOrder;
use app\models\Goods;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\models\Order;
use app\models\UserInfo;
use app\notification\jobs\OrderPaymentSuccessNotificationWeTplJob;
use app\notification\wechat_template_message\OrderPaymentSuccessNotificationWeTplMsg;

class OrderPaymentSuccessNotification
{

    public static function send(Order $order)
    {
        (new OrderPaymentSuccessNotificationWeTplJob([
            "order" => $order
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new OrderPaymentSuccessNotificationWeTplJob([
            "order" => $order
        ]));*/
    }

    public static function sendWechatTemplate(Order $order)
    {
        if ($order->mch_id) {
            $store = Store::findOne(["mch_id" => $order->mch_id]);
            if(!$store) return;

            $store_name = $store->name;
        } else {
            $store_name = '补商汇';
        }

        $user = User::findOne($order->user_id);
        if(!$user) return;

        $order_detail = OrderDetail::findOne(['order_id' => $order->id]);
        if(!$order_detail) return;

        $goods = Goods::find()->with('goodsWarehouse')->where([
            'id'        => $order_detail->goods_id,
            'mall_id'   => $order->mall_id,
            'status'    => 1,
            'is_delete' => 0,
        ])->one();
        if(!$goods) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new OrderPaymentSuccessNotificationWeTplMsg([
            "mall_id"           => $order->mall_id,
            "title"             => '您好，您在'. $store_name .'下单成功了',
            "openid"            => $userInfo->openid,
            "order_no"          => $order->order_no,
            "goods_name"        => $goods->goodsWarehouse->name,
            "total_pay_price"   => $order->total_pay_price,
            "store"             => $store_name,
            "pay_at"            => $order->pay_at
        ]))->send();


    }
}