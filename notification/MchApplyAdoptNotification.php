<?php

namespace app\notification;

use app\helpers\SerializeHelper;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchApplyAdoptNotificationWeTplJob;
use app\notification\wechat_template_message\MchApplyWeTplMsg;
use app\plugins\mch\models\MchApply;

class MchApplyAdoptNotification
{

    public static function send(MchApply $mch_apply)
    {
        (new MchApplyAdoptNotificationWeTplJob([
            "mch_apply" => $mch_apply
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new MchApplyAdoptNotificationWeTplJob([
            "mch_apply" => $mch_apply
        ]));*/
    }

    public static function sendWechatTemplate(MchApply $mch_apply)
    {
        if (!$mch_apply->json_apply_data) return;

        $apply_data = SerializeHelper::decode($mch_apply->json_apply_data);

        $user = User::findOne($mch_apply->user_id);
        if (!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if (!$userInfo) return;

        (new MchApplyWeTplMsg([
            "mall_id"       => $mch_apply->mall_id,
            "openid"        => $userInfo->openid,
            "template_id"   => TemConfig::ShopApproved,
            "data"          => [
                'first'    => '店铺审核已通过',
                'keyword1' => isset($apply_data['store_name']) ? $apply_data['store_name'] : '',
                'keyword2' => $user->nickname . "[".$user->id."]",
                'keyword3' => date("Y-m-d H:i", $mch_apply->updated_at),
                'remark'   => '如有疑问请联系020-31923526'
            ],
        ]))->send();
    }
}