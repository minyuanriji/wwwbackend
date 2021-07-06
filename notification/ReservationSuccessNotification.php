<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\ReservationSuccessNotificationWeTplJob;
use app\notification\wechat_template_message\ReservationSuccessWeTplMsg;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class ReservationSuccessNotification
{

    public static function send(HotelOrder $hotel_order)
    {
        (new ReservationSuccessNotificationWeTplJob([
            "hotel_order" => $hotel_order
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new ReservationSuccessNotificationWeTplJob([
            "hotel_order" => $hotel_order
        ]));*/
    }

    public static function sendWechatTemplate(HotelOrder $hotel_order)
    {
        $user = User::findOne($hotel_order->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $template_id = TemConfig::WithdrawalFailure;
        $data = [
            'first'     => '您已完成支付，酒店预订成功。',
            'keyword1'  => $mch_cash->money,
            'keyword2'  => date('Y-m-d H:i:s', $mch_cash->updated_at),
            'keyword3'  => '已退回',
            'keyword4'  => $mch_cash->content,
            'keyword5'  => $mch_cash->content,
            'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
        ];

        (new ReservationSuccessWeTplMsg([
            "mall_id"           => $mch_cash->mall_id,
            "openid"            => $userInfo->openid,
            "data"              => $data,
            "template_id"       => $template_id,
        ]))->send();
    }
}