<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\GiftPayVoucherNotificationWeTplJob;
use app\notification\wechat_template_message\GiftPayVoucherWeTplMsg;
use app\plugins\giftpacks\models\Giftpacks;
use app\plugins\giftpacks\models\GiftpacksOrder;

class GiftPayVoucherNotification
{
    public static function send($voucher_log)
    {
        /*(new GiftPayVoucherNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new GiftPayVoucherNotificationWeTplJob([
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

        $giftpacksOrderResult = GiftpacksOrder::findOne($voucher_log['source_id']);
        if(!$giftpacksOrderResult) return;

        $giftpacksResult = Giftpacks::findOne($giftpacksOrderResult->pack_id);
        if(!$giftpacksResult) return;

        (new GiftPayVoucherWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的大礼包订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $giftpacksOrderResult->order_sn,
                'keyword3'  => $giftpacksOrderResult->order_price . '元',
                'keyword4'  => '大礼包：' . $giftpacksResult->title,
                'remark'    => '赠送’'. $voucher_log['money'] .'‘红包，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
