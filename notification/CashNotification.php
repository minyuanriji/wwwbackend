<?php
namespace app\notification;

use app\forms\efps\EfpsTransfer;
use app\helpers\SerializeHelper;
use app\models\Cash;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\CashNotificationWeTplJob;
use app\notification\wechat_template_message\CashNotificationWeTplMsg;

class CashNotification
{

    public static function send(Cash $cash)
    {
        (new CashNotificationWeTplJob([
            "cash" => $cash
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new CashNotificationWeTplJob([
            "cash" => $cash
        ]));*/
    }

    public static function sendWechatTemplate(Cash $cash)
    {
        $user = User::findOne($cash->user_id);
        if(!$user) return;

        $data = [];
        $template_id = '';
        switch ($cash->status)
        {
            //同意
            case Cash::STATUS_AGREE:
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
                break;

            //已打款
            case Cash::STATUS_PAID:
                $res = EfpsTransfer::query($cash->order_no);
                if ($res['code'] == 0) {
                    $template_id = TemConfig::NoticeOfWithdrawal;
                    $data = [
                        'first'     => '您申请的提现金额已到帐！',
                        'keyword1'  => date('Y-m-d H:i:s', $cash->updated_at),
                        'keyword2'  => '银行卡转帐',
                        'keyword3'  => $cash->price,
                        'keyword4'  => $cash->price - $cash->fact_price,
                        'keyword5'  => $cash->fact_price,
                        'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
                    ];
                } else {
                    $template_id = TemConfig::WithdrawalFailure;
                    $data = [
                        'first'     => '您的提现申请失败，资金已原路退回！',
                        'keyword1'  => $cash->price,
                        'keyword2'  => date('Y-m-d H:i:s', $cash->updated_at),
                        'keyword3'  => '已退回',
                        'keyword4'  => $res['msg'],
                        'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
                    ];
                }
                break;

            //驳回
            case Cash::STATUS_REJECT:
                $template_id = TemConfig::WithdrawalFailure;
                $content = SerializeHelper::decode($cash->content);
                $data = [
                    'first'     => '您的提现申请失败，资金已原路退回！',
                    'keyword1'  => $cash->price,
                    'keyword2'  => date('Y-m-d H:i:s', $cash->updated_at),
                    'keyword3'  => '已退回',
                    'keyword4'  => $content['reject_content'] ? $content['reject_content'] : '',
                    'remark'    => '感谢您的使用！如有疑问请联系客服020-31923526',
                ];
                break;

            default:
                break;
        }

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