<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\HotelPayVoucherNotificationWeTplJob;
use app\notification\wechat_template_message\HotelPayVoucherWeTplMsg;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\hotel\models\HotelOrder;

class HotelPayVoucherNotification
{
    public static function send($voucher_log)
    {
        /*(new HotelPayVoucherNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new HotelPayVoucherNotificationWeTplJob([
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

        $hotelOrderResult = HotelOrder::findOne($voucher_log['source_id']);
        if(!$hotelOrderResult) return;

        (new HotelPayVoucherWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的酒店订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $hotelOrderResult->order_no,
                'keyword3'  => $hotelOrderResult->order_price . '元',
                'keyword4'  => '酒店下单:' . $hotelOrderResult->order_price . '元',
                'remark'    => '赠送’'. $voucher_log['money'] .'‘购物券，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
