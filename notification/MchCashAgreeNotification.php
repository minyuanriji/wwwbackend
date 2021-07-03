<?php

namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchCashAgreeNotificationWeTplJob;
use app\notification\wechat_template_message\MchCashNotificationWeTplMsg;
use app\plugins\mch\models\Mch;
use app\plugins\mch\models\MchCash;

class MchCashAgreeNotification
{

    public static function send(MchCash $mch_cash)
    {
        (new MchCashAgreeNotificationWeTplJob([
            "mch_cash" => $mch_cash
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new MchCashAgreeNotificationWeTplJob([
            "mch_cash" => $mch_cash
        ]));*/
    }

    public static function sendWechatTemplate(MchCash $mch_cash)
    {
        $mch = Mch::findOne(['id' => $mch_cash->mch_id, 'review_status' => 1]);
        if(!$mch) return;
        $user = User::findOne($mch->user_id);
        if(!$user) return;

        $res = EfpsTransfer::query($mch_cash->order_no);
        if ($res['code'] == 0) {
            $template_id = TemConfig::NoticeOfWithdrawal;
            $data = [
                'first'     => '您申请的提现金额已到帐！',
                'keyword1'  => date('Y-m-d H:i:s', $mch_cash->updated_at),
                'keyword2'  => '银行卡转帐',
                'keyword3'  => $mch_cash->money,
                'keyword4'  => $mch_cash->money - $mch_cash->fact_price,
                'keyword5'  => $mch_cash->fact_price,
                'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
            ];
        } else {
            $template_id = TemConfig::WithdrawalFailure;
            $data = [
                'first'     => '您的提现申请失败，资金已原路退回！',
                'keyword1'  => $mch_cash->money,
                'keyword2'  => date('Y-m-d H:i:s', $mch_cash->updated_at),
                'keyword3'  => '已退回',
                'keyword4'  => $mch_cash->content,
                'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
            ];
        }

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new MchCashNotificationWeTplMsg([
            "mall_id"           => $mch_cash->mall_id,
            "openid"            => $userInfo->openid,
            "data"              => $data,
            "template_id"       => $template_id,
        ]))->send();
    }
}