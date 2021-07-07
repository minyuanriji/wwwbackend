<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\HotelRefundSuccessfulNotificationWeTplJob;
use app\notification\wechat_template_message\HotelWeTplMsg;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class HotelRefundSuccessfulNotification
{
    public static function send(HotelRefundApplyOrder $hotel_refund_order)
    {
        (new HotelRefundSuccessfulNotificationWeTplJob([
            "hotel_refund_order" => $hotel_refund_order
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new HotelRefundSuccessfulNotificationWeTplJob([
            "hotel_refund_order" => $hotel_refund_order
        ]));*/
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

        print_r([
            'first'     => '您的酒店订单号为' . $hotel_order_res->order_no . '售后已退款成功',
            'keyword1'  => $hotel_order_res->pay_price,
            'keyword2'  => '原路退回',
            'keyword3'  => '具体到账时间以收到时间为准',
            'remark'    => '若部分退款或特殊退款要求则以工作人员确认的为准 若有疑问请拨打020-31923526',
        ]);die;

        (new HotelWeTplMsg([
            "mall_id"           => $hotel_order_res->mall_id,
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::RefundSuccessNotification,
            "data"              => [
                'first'     => '您的酒店订单号为' . $hotel_order_res->order_no . '售后已退款成功',
                'keyword1'  => $hotel_order_res->pay_price,
                'keyword2'  => '原路退回',
                'keyword3'  => '具体到账时间以收到时间为准',
                'remark'    => '若部分退款或特殊退款要求则以工作人员确认的为准 若有疑问请拨打020-31923526',
            ]
        ]))->send();
    }
}