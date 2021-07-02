<?php
namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\helpers\SerializeHelper;
use app\models\Cash;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\OrderRefundRefuseNotificationWeTplJob;
use app\notification\wechat_template_message\OrderRefundRefuseNotificationWeTplMsg;
use function Composer\Autoload\includeFile;

class OrderRefundRefuseNotification
{
    const type = [
        '退款',
        '退款退货',
        '换货',
    ];

    public static function send(OrderRefund $order_refund)
    {
        (new OrderRefundRefuseNotificationWeTplJob([
            "order_refund" => $order_refund
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new OrderRefundRefuseNotificationWeTplJob([
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

        (new OrderRefundRefuseNotificationWeTplMsg([
            "mall_id"           => $order_refund->mall_id,
            "openid"            => $userInfo->openid,
            "first"             => '您好，商家暂时拒绝您的'. self::type[$order_refund->type] .'申请',
            "order_no"          => $order->order_no,
            "refund_no"         => $order_refund->order_no,
            "reasons_refusal"   => $order_refund->merchant_remark,
            "remark"            => '您可联系商家后再发起退款申请,如有疑问请联系020-31923526!'
        ]))->send();
    }
}