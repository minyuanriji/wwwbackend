<?php

namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchApplyAdoptNotificationWeTplJob;
use app\notification\wechat_template_message\MchApplyWeTplMsg;
use app\plugins\mch\models\Mch;

class MchApplyAdoptNotification
{

    public static function send(Mch $mch)
    {
        /*(new MchApplyAdoptNotificationWeTplJob([
            "mch" => $mch
        ]))->execute(null);*/

        \Yii::$app->queue->delay(0)->push(new MchApplyAdoptNotificationWeTplJob([
            "mch" => $mch
        ]));
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

        $template_id = TemConfig::ShopApproved;
        $data = [
            'first'    => '店铺审核已通过',
            'keyword1' => $store->name,
            'keyword2' => $user->nickname . "[".$user->id."]",
            'keyword3' => date("Y-m-d H:i", $mch->updated_at),
            'remark'   => '如有疑问请联系020-31923526'
        ];

        (new MchApplyWeTplMsg([
            "mall_id" => $mch->mall_id,
            "openid" => $userInfo->openid,
            "template_id" => $template_id,
            "data" => $data,
        ]))->send();
    }
}