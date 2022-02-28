<?php

namespace app\notification;

use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\OilPayVoucherNotificationWeTplJob;
use app\notification\wechat_template_message\OilPayVoucherWeTplMsg;
use app\plugins\oil\models\OilOrders;

class OilPayVoucherNotification
{
    public static function send($voucher_log)
    {
        /*(new OilPayVoucherNotificationWeTplJob([
            "voucher_log" => $voucher_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new OilPayVoucherNotificationWeTplJob([
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

        $oilOrdersResult = OilOrders::findOne($voucher_log['source_id']);
        if(!$oilOrdersResult) return;

        (new OilPayVoucherWeTplMsg([
            "mall_id"           => $voucher_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::GIVE_SHOPPING_VOUCHER,
            "data"              => [
                'first'     => '您的加油订单已支付成功。',
                'keyword1'  => $user->nickname,
                'keyword2'  => $oilOrdersResult->order_no,
                'keyword3'  => $oilOrdersResult->order_price . '元',
                'keyword4'  => '加油订单',
                'remark'    => '赠送’'. $voucher_log['money'] .'‘红包，欢迎您再次光临！'
            ]
        ]))->send();
    }
}
