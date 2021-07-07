<?php

namespace app\notification;

use app\models\User;
use app\models\UserInfo;
use app\notification\jobs\AddOfflineNotificationWeTplJob;
use app\notification\wechat_template_message\UserWeTplMsg;

class AddOfflineNotification
{

    public static function send(User $user)
    {
        (new AddOfflineNotificationWeTplJob([
            "user" => $user
        ]))->execute(null);

        /*\Yii::$app->queue->delay(0)->push(new AddOfflineNotificationWeTplJob([
            "user" => $user
        ]));*/
    }

    public static function sendWechatTemplate(User $user)
    {
        //上级通知
        $parent_user = User::findOne($user->parent_id);
        if(!$parent_user) return;
        $parent_user_info = UserInfo::findOne([
            "user_id" => $parent_user->id,
            "platform" => "wechat"
        ]);
        if(!$parent_user_info) return;

        //上上级通知
        $upper_upper_user_info = null;
        if ($parent_user->parent_id) {
            $upper_upper_user = User::findOne($parent_user->parent_id);
            if ($upper_upper_user) {
                $upper_upper_user_info = UserInfo::findOne([
                    "user_id" => $upper_upper_user->id,
                    "platform" => "wechat"
                ]);
            }
        }
        if ($upper_upper_user_info) {
            $open_id_array = [
                [
                    'id' => $parent_user->id,
                    'open_id' => $parent_user_info->openid,
                ],
                [
                    'id' => $upper_upper_user->id,
                    'open_id' => $upper_upper_user_info->openid,
                ]
            ];
        } else {
            $open_id_array = [
                [
                    'id' => $parent_user->id,
                    'open_id' => $parent_user_info->openid,
                ]
            ];
        }

        foreach ($open_id_array as $open_id) {
            print_r([
                'first'     => '您有新的成员加入！',
                'keyword1'  => $open_id['id'],
                'keyword2'  => date('Y-m-d H:i:s', $user->junior_at),
                'keyword3'  => $user->nickname,
                'remark'    => '',
            ]);die;
            (new UserWeTplMsg([
                "mall_id"           => $user->mall_id,
                "openid"            => $open_id['open_id'],
                "template_id"       => TemConfig::OfflineJoinNotice,
                "data"              => [
                    'first'     => '您有新的成员加入！',
                    'keyword1'  => $open_id['id'],
                    'keyword2'  => date('Y-m-d H:i:s', $user->junior_at),
                    'keyword3'  => $user->nickname,
                    'remark'    => '',
                ]
            ]))->send();

            sleep(2);
        }
    }
}