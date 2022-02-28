<?php

namespace app\notification;

use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\GoodsPayVoucherNotificationWeTplJob;
use app\notification\wechat_template_message\GoodsPayVoucherWeTplMsg;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GoodsPayVoucherNotification
{
    public static function send($voucher_log)
    {
        /*(new GoodsPayVoucherNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new GoodsPayVoucherNotificationWeTplJob([
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

        $orderDetailResult = OrderDetail::findOne($voucher_log['source_id']);
        if(!$orderDetailResult) return;

        $orderResult = Order::findOne($orderDetailResult->order_id);
        if(!$orderResult) return;

        $goodsResult = Goods::findOne($orderDetailResult->goods_id);
        if(!$goodsResult) return;

        $goodsWarehouseResult = GoodsWarehouse::findOne($goodsResult->goods_warehouse_id);
        if(!$goodsWarehouseResult) return;

        (new GoodsPayVoucherWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的商品订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $orderResult->order_no,
                'keyword3'  => $orderDetailResult->total_price . '元',
                'keyword4'  => $goodsWarehouseResult->name,
                'remark'    => '赠送’'. $voucher_log['money'] .'‘红包，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
