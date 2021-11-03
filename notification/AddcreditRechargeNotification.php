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

        $userVoucher = ShoppingVoucherUser::findOne(['user_id' => $voucher_log['user_id']]);
        if(!$userVoucher) return;

        (new AddcreditRechargeWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::VOUCHER_ORDER_PAY,
            "data"              => [
                'first'     => '尊敬的用户，您好！您已消费成功，详情如下：',
                'keyword1'  => $store->name,
                'keyword2'  => $addcreditOrder->order_price . '元',
                'keyword3'  => $voucher_log['money'] . '购物券',
                'keyword4'  => date('Y-m-d H:i:s', $addcreditOrder->pay_at),
                'keyword5'  => $userVoucher->money . '购物券',
                'remark'    => '欢迎您再次光临！'
            ]
        ]))->send();
    }
}