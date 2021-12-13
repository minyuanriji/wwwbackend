<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\ShoppingVoucherIncomeNotificationWeTplJob;
use app\notification\wechat_template_message\ShoppingVoucherIncomeWeTplMsg;

class ShoppingVoucherIncomeNotification
{
    public static function send($voucher_log)
    {
        \Yii::$app->queue->delay(0)->push(new ShoppingVoucherIncomeNotificationWeTplJob([
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

        (new ShoppingVoucherIncomeWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::CONSUMPTION_SUCCESS_REMINDER,
            "data"              => [
                'first'     => '您好，您已消费成功',
                'keyword1'  => $user->nickname,
                'keyword2'  => date('Y-m-d H:i:s', $voucher_log['created_at']),
                'remark'    => $voucher_log['desc'] . '’'. $voucher_log['money'] .'‘，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
