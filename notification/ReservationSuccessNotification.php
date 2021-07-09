<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\ReservationSuccessNotificationWeTplJob;
use app\notification\wechat_template_message\HotelWeTplMsg;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\Hotels;

class ReservationSuccessNotification
{

    const hotel_type = [
        'luxe'      => '豪华型',
        'comfort'   => '舒适型',
        'eco'       => '经济型'
    ];

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

        $hotel = Hotels::findOne($hotel_order->hotel_id);
        if(!$hotel) return;

        (new HotelWeTplMsg([
            "mall_id"           => $hotel_order->mall_id,
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::HotelReservationSuccessful,
            "data"              => [
                'first'     => '您已完成支付，酒店预订成功。',
                'keyword1'  => $hotel->name,
                'keyword2'  => self::hotel_type[$hotel->type],
                'keyword3'  => $hotel_order->booking_start_date,
                'keyword4'  => $hotel_order->booking_num,
                'keyword5'  => $hotel_order->order_price,
                'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
            ],
        ]))->send();
    }
}