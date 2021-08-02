<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\HotelAfterSalesRejectionNotificationWeTplJob;
use app\notification\wechat_template_message\HotelWeTplMsg;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;
use app\plugins\hotel\models\Hotels;

class HotelAfterSalesRejectionNotification
{

    const hotel_type = [
        'luxe'      => '豪华型',
        'comfort'   => '舒适型',
        'eco'       => '经济型'
    ];

    public static function send(HotelRefundApplyOrder $hotel_refund_order)
    {
        /*(new HotelAfterSalesRejectionNotificationWeTplJob([
            "hotel_refund_order" => $hotel_refund_order
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new HotelAfterSalesRejectionNotificationWeTplJob([
            "hotel_refund_order" => $hotel_refund_order
        ]));
    }

    public static function sendWechatTemplate(HotelRefundApplyOrder $hotel_refund_order)
    {
        $hotel_order_res = HotelOrder::findOne($hotel_refund_order->order_id);
        if(!$hotel_order_res) return;

        $user = User::findOne($hotel_order_res->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $hotel = Hotels::findOne($hotel_order_res->hotel_id);
        if(!$hotel) return;

        (new HotelWeTplMsg([
            "mall_id"           => $hotel_order_res->mall_id,
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::OrderCancellationFailed,
            "data"              => [
                'first'     => '抱歉，您的酒店订单取消失败',
                'keyword1'  => $hotel_order_res->order_no,
                'keyword2'  => $hotel->name,
                'keyword3'  => self::hotel_type[$hotel->type],
                'keyword4'  => $hotel->booking_num,
                'keyword5'  => $hotel_order_res->booking_start_date,
                'remark'    => $hotel_refund_order->remark ? $hotel_refund_order->remark . ',' : '' . '感谢您的使用！如有疑问请联系客服020-31923526',
            ],
        ]))->send();
    }
}