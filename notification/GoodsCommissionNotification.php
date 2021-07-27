<?php

namespace app\notification;

use app\models\IncomeLog;
use app\models\Order;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\GoodsCommissionNotificationWeTplJob;
use app\notification\wechat_template_message\CommissionWeTplMsg;
use app\plugins\commission\models\CommissionGoodsPriceLog;

class GoodsCommissionNotification
{
    public static function send(IncomeLog $income_log)
    {
        /*(new GoodsCommissionNotificationWeTplJob([
            "income_log" => $income_log
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new GoodsCommissionNotificationWeTplJob([
            "income_log" => $income_log
        ]));
    }

    public static function sendWechatTemplate(IncomeLog $income_log)
    {
        $user = User::findOne($income_log->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        $commission_goods = CommissionGoodsPriceLog::findOne($income_log->source_id);
        if(!$commission_goods) return;

        $order = Order::findOne($commission_goods->order_id);
        if(!$order) return;

        $order_user = User::findOne($order->user_id);
        if(!$order_user) return;

        $data = [
            'first'     => '您的下级成功支付订单，您获得一笔分润哟',
            'keyword1'  => $order_user->nickname,
            'keyword2'  => $order->order_no,
            'keyword3'  => $order->total_goods_original_price,
            'keyword4'  => $income_log->income,
            'keyword5'  => date('Y-m-d H:i:s', $order->pay_at),
            'remark'    => $income_log->desc,
        ];

        (new CommissionWeTplMsg([
            "mall_id"           => $income_log->mall_id,
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::SubOrderSubCommission,
            "data"              => $data
        ]))->send();
    }
}