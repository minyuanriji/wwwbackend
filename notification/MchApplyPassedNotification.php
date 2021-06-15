<?php
namespace app\notification;

use app\models\Store;
use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\MchApplyPassedNotificationWeTplJob;
use app\notification\wechat_template_message\MchApplyPassedWeTplMsg;
use app\plugins\mch\models\Mch;

class MchApplyPassedNotification{

    public static function send(Mch $mch){
        \Yii::$app->queue->delay(0)->push(new MchApplyPassedNotificationWeTplJob([
            "mch" => $mch
        ]));
        //static::sendWechatTemplate($mch);
    }

    public static function sendWechatTemplate(Mch $mch){
        $store = Store::findOne(["mch_id" => $mch->id]);
        if(!$store) return;

        $user = User::findOne($mch->user_id);
        if(!$user) return;

        $userInfo = UserInfo::findOne([
            "user_id" => $user->id,
            "platform" => "wechat"
        ]);
        if(!$userInfo) return;

        (new MchApplyPassedWeTplMsg([
            "mall_id"    => $mch->mall_id,
            "openid"     => $userInfo->openid,
            "name"       => $store->name,
            "nickname"   => $user->nickname,
            "updated_at" => $mch->updated_at,
            "user_id"    => $user->id
        ]))->send();


    }
}