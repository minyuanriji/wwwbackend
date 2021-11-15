<?php

namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\AddcreditRechargeNotificationWeTplJob;
use app\notification\wechat_template_message\AddcreditRechargeWeTplMsg;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class AddcreditRechargeNotification
{
    public static function send($voucher_log)
    {
        /*(new AddcreditRechargeNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new AddcreditRechargeNotificationWeTplJob([
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

        $addcreditOrder = AddcreditOrder::findOne($voucher_log['source_id']);
        if(!$addcreditOrder) return;

        (new AddcreditRechargeWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的话费订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $addcreditOrder->order_no,
                'keyword3'  => $addcreditOrder->order_price . '元',
                'keyword4'  => '话费充值:' . $addcreditOrder->order_price . '元',
                'remark'    => '赠送’'. $voucher_log['money'] .'‘购物券，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
