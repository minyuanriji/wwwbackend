<?php
namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\helpers\SerializeHelper;
use app\models\Cash;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\CashRejectNotificationWeTplJob;
use app\notification\wechat_template_message\CashNotificationWeTplMsg;

class CashRejectNotification
{

    public static function send(Cash $cash)
    {
        /*(new CashRejectNotificationWeTplJob([
            "cash" => $cash
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new CashRejectNotificationWeTplJob([
            "cash" => $cash
        ]));
    }

    public static function sendWechatTemplate(Cash $cash)
    {
        $template_id = TemConfig::WithdrawalFailure;
        $content = SerializeHelper::decode($cash->content);
        $data = [
            'first'     => '您的提现申请失败，资金已原路退回！',
            'keyword1'  => $cash->fact_price,
            'keyword2'  => date('Y-m-d H:i:s', $cash->updated_at),
            'keyword3'  => '已退回',
            'keyword4'  => $content['reject_content'] ? $content['reject_content'] : '',
            'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
        ];

        $user = User::findOne($cash->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new CashNotificationWeTplMsg([
            "mall_id"           => $cash->mall_id,
            "openid"            => $userInfo->openid,
            "data"              => $data,
            "template_id"       => $template_id,
        ]))->send();
    }
}