<?php
namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\helpers\SerializeHelper;
use app\models\Cash;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\CashAgreeNotificationWeTplJob;
use app\notification\wechat_template_message\CashNotificationWeTplMsg;

class CashAgreeNotification
{

    public static function send(Cash $cash)
    {
        (new CashAgreeNotificationWeTplJob([
            "cash" => $cash
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new CashAgreeNotificationWeTplJob([
            "cash" => $cash
        ]));*/
    }

    public static function sendWechatTemplate(Cash $cash)
    {
        $user = User::findOne($cash->user_id);
        if(!$user) return;

        $template_id = TemConfig::SuccessfulWithdrawalApplication;
        $extra = SerializeHelper::decode($cash->extra);
        if (!$extra['bank_account'] || !is_string($extra['bank_account'])) return;
        $data = [
            'first'     => '恭喜，您的提现已转银行处理，具体到账时间以银行为准，请注意查收~~',
            'keyword1'  => $cash->price,
            'keyword2'  => date('Y-m-d H:i:s', $cash->updated_at),
            'keyword3'  => $extra['bank_name'] . '(尾号' . substr($extra['bank_account'],-4) . ')',
            'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
        ];

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