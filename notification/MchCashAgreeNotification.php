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
        /*(new MchCashAgreeNotificationWeTplJob([
            "mch_cash" => $mch_cash
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new MchCashAgreeNotificationWeTplJob([
            "mch_cash" => $mch_cash
        ]));
    }

    public static function sendWechatTemplate(MchCash $mch_cash)
    {
        $mch = Mch::findOne(['id' => $mch_cash->mch_id, 'review_status' => 1]);
        if(!$mch) return;
        $user = User::findOne($mch->user_id);
        if(!$user) return;

        $res = EfpsTransfer::query($mch_cash->order_no);
        if ($res['code'] == 0) {
            $template_id = TemConfig::NoticeOfWithdrawalAndReceipt;
            $extra = json_decode($mch_cash->type_data,true);
            $data = [
                'first'     => '您好，您的商户提现已到账！',
                'keyword1'  => $mch_cash->fact_price,
                'keyword2'  => $extra['bankName'] . "尾号：" . substr($extra['bankCardNo'], -4),
                'keyword3'  => date('Y-m-d H:i:s', $mch_cash->updated_at),
                'keyword4'  => '具体到账时间以银行时间为准！',
                'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
            ];
        } else {
            $template_id = TemConfig::WithdrawalFailure;
            $data = [
                'first'     => '您的提现申请失败！',
                'keyword1'  => $mch_cash->fact_price,
                'keyword2'  => date('Y-m-d H:i:s', $mch_cash->updated_at),
                'keyword3'  => '待审核',
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