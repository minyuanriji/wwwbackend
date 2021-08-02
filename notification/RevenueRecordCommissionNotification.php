<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\RevenueRecordCommissionNotificationWeTplJob;
use app\notification\wechat_template_message\CommissionWeTplMsg;

class RevenueRecordCommissionNotification
{
    public static function send($income_log)
    {
        (new RevenueRecordCommissionNotificationWeTplJob([
            "income_log" => $income_log
        ]))->execute(null);

    //    \Yii::$app->queue->delay(0)->push(new RevenueRecordCommissionNotificationWeTplJob([
  //          "income_log" => $income_log
//        ]));
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

        switch ($income_log['source_type'])
        {
            case 'store':
                $keyword2 = '推荐门店分佣';
                break;
            case 'checkout':
                $keyword2 = '推荐门店分佣';
                break;
            case 'boss':
                $keyword2 = '股东分红';
                break;
            case 'hotel_commission':
                $keyword2 = '推荐酒店分佣';
                break;
            case 'hotel_3r_commission':
                $keyword2 = '酒店消费分佣';
                break;
            case 'goods':
                $keyword2 = '商品消费分佣';
                break;
            case 'giftpacks_commission':
                $keyword2 = '大礼包消费分佣';
                break;
            case 'addcredit':
                $keyword2 = '话费直推分佣';
                break;
            case 'addcredit_3r':
                $keyword2 = '话费消费分佣';
                break;
            default:
                break;
        }

        (new CommissionWeTplMsg([
            "mall_id"           => $income_log['mall_id'],
            "openid"            => $userInfo->openid,
            "template_id"       => TemConfig::IncomeArrival,
            "data"              => [
                'first'     => '您有一笔新的收益哟',
                'keyword1'  => $income_log['income'],
                'keyword2'  => $keyword2,
                'keyword3'  => date('Y-m-d H:i:s', time()),
                'remark'    => $income_log['desc']
            ]
        ]))->send();
    }
}
