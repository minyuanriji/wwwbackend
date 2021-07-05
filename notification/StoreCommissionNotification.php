<?php

namespace app\notification;

use app\models\IncomeLog;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\StoreCommissionNotificationWeTplJob;
use app\notification\wechat_template_message\CommissionWeTplMsg;
use app\plugins\commission\models\CommissionStorePriceLog;
use app\plugins\mch\models\MchCheckoutOrder;

class StoreCommissionNotification
{
    public static function send($income_log)
    {
        (new StoreCommissionNotificationWeTplJob([
            "income_log" => $income_log
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new StoreCommissionNotificationWeTplJob([
            "income_log" => $income_log
        ]));*/
    }

    public static function sendWechatTemplate($income_log)
    {
        $user = User::findOne($income_log['user_id']);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $commission_store = CommissionStorePriceLog::findOne($income_log['source_id']);
        if(!$commission_store) return;

        $checkout_order = MchCheckoutOrder::findOne($commission_store->item_id);
        if(!$checkout_order) return;

        $order_user = User::findOne($checkout_order->pay_user_id);
        if(!$order_user) return;

        $data = [
            'first'     => '您的下级门店有新的支付订单，您获得一笔分润哟！',
            'keyword1'  => $order_user->nickname,
            'keyword2'  => $checkout_order->order_no,
            'keyword3'  => $checkout_order->order_price,
            'keyword4'  => $income_log['income'],
            'keyword5'  => date('Y-m-d H:i:s', $checkout_order->pay_at),
            'remark'    => '感谢你的使用,如有疑问请联系020-31923526',
        ];

        (new CommissionWeTplMsg([
            "mall_id"           => $income_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::SubOrderSubCommission,
            "data"              => $data
        ]))->send();
    }
}