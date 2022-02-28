<?php

namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\StorePayVoucherNotificationWeTplJob;
use app\notification\wechat_template_message\StorePayVoucherWeTplMsg;
use app\plugins\mch\models\MchCheckoutOrder;
use app\plugins\shopping_voucher\models\ShoppingVoucherUser;

class StorePayVoucherNotification
{
    public static function send($voucher_log)
    {
        /*(new StorePayVoucherNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new StorePayVoucherNotificationWeTplJob([
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

        $mchCheckoutOrder = MchCheckoutOrder::findOne($voucher_log['source_id']);
        if(!$mchCheckoutOrder) return;

        $store = Store::findOne(['mch_id' => $mchCheckoutOrder->mch_id, 'is_delete' => 0]);
        if(!$store) return;

        (new StorePayVoucherWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的门店订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $mchCheckoutOrder->order_no,
                'keyword3'  => $mchCheckoutOrder->order_price . '元',
                'keyword4'  => '门店：' . $store->name,
                'remark'    => '赠送’'. $voucher_log['money'] .'‘红包，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
