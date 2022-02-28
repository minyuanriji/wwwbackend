<?php

namespace app\notification;

use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\VoucherConsumptionNotificationWeTplJob;
use app\notification\wechat_template_message\VoucherConsumptionWeTplMsg;

class VoucherConsumptionNotification
{
    public static function send($voucher_log)
    {
        /*(new VoucherConsumptionNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new VoucherConsumptionNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]));
    }

    public static function sendWechatTemplate($voucher_log)
    {
        $user = User::findOne($voucher_log['user_id']);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $order = Order::findOne($voucher_log['source_id']);
        if(!$order) return;

        $orderDetail = OrderDetail::findOne(['order_id' => $order->id]);
        if(!$order) return;

        $goods = Goods::findOne($orderDetail->goods_id);
        if(!$goods) return;

        $goodWare = GoodsWarehouse::findOne($goods->goods_warehouse_id);
        if(!$goodWare) return;

        (new VoucherConsumptionWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::STORE_PAY,
            "data"              => [
                'first'     => '恭喜您！购买的商品已支付成功，请留意物流信息哦！么么哒！~~',
                'keyword1'  => $order->order_no,
                'keyword2'  => $goodWare->name,
                'keyword3'  => $orderDetail->shopping_voucher_num . '红包',
                'keyword4'  => '已支付',
                'keyword5'  => date('Y-m-d H:i:s', $order->pay_at),
                'remark'    => '欢迎您的到来！'
            ]
        ]))->send();
    }
}
