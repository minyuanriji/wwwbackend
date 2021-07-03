<?php

namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchApplyNoPassNotificationWeTplJob;
use app\notification\wechat_template_message\MchApplyWeTplMsg;
use app\plugins\mch\models\Mch;

class MchApplyNoPassNotification
{

    public static function send(Mch $mch)
    {
        (new MchApplyNoPassNotificationWeTplJob([
            "mch" => $mch
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new MchApplyNoPassNotificationWeTplJob([
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

        $template_id = TemConfig::FailedPassAudit;
        $data = [
            'first' => '抱歉，门店审核未通过！',
            'keyword1' => $store->name,
            'keyword2' => $user->nickname . "[".$user->id."]",
            'keyword3' => $mch->review_remark,
            'keyword4' => date("Y-m-d H:i", $mch->updated_at),
            'remark' => '请完善后再次提交',
        ];
        (new MchApplyWeTplMsg([
            "mall_id" => $mch->mall_id,
            "openid" => $userInfo->openid,
            "template_id" => $template_id,
            "data" => $data,
        ]))->send();
    }
}