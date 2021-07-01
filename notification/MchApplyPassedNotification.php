<?php

namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchApplyPassedNotificationWeTplJob;
use app\notification\wechat_template_message\MchApplyPassedWeTplMsg;
use app\plugins\mch\models\Mch;

class MchApplyPassedNotification
{

    public static function send(Mch $mch)
    {
        (new MchApplyPassedNotificationWeTplJob([
            "mch" => $mch
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new MchApplyPassedNotificationWeTplJob([
            "mch" => $mch
        ]));*/
    }

    public static function sendWechatTemplate(Mch $mch)
    {
        $store = Store::findOne(["mch_id" => $mch->id]);
        if (!$store) return;

        $user = User::findOne($mch->user_id);
        if (!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if (!$userInfo) return;

        $template_id = '';
        $data = [];
        if ($mch->review_status == Mch::REVIEW_STATUS_CHECKED) {
            $template_id = TemConfig::ShopApproved;
            $data = [
                'first'    => '店铺审核已通过',
                'keyword1' => $store->name,
                'keyword2' => $user->nickname . "[".$user->id."]",
                'keyword3' => date("Y-m-d H:i", $mch->updated_at),
                'remark'   => '如有疑问请联系020-31923526'
            ];
        } elseif ($mch->review_status == Mch::REVIEW_STATUS_NOTPASS) {
            $template_id = TemConfig::FailedPassAudit;
            $data = [
                'first' => '抱歉，门店审核未通过！',
                'keyword1' => $store->name,
                'keyword2' => $user->nickname . "[".$user->id."]",
                'keyword3' => $mch->review_remark,
                'keyword4' => date("Y-m-d H:i", $mch->updated_at),
                'remark' => '请完善后再次提交',
            ];
        }
        (new MchApplyPassedWeTplMsg([
            "mall_id" => $mch->mall_id,
            "openid" => $userInfo->openid,
            "template_id" => $template_id,
            "data" => $data,
        ]))->send();
    }
}
