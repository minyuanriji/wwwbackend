<?php

namespace app\handlers;

/**
 * 用户注册事件
 */

use app\events\UserEvent;
use app\forms\common\coupon\CouponAutoSendCommon;
use app\helpers\sms\Sms;
use app\logic\AppConfigLogic;
use app\models\ErrorLog;
use app\models\User;
use app\services\wechat\WechatTemplateService;
use yii\helpers\ArrayHelper;

class MyHandler extends BaseHandler
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(User::EVENT_REGISTER, function ($event) {
            $user     = $event->user;
            $nickname = $this->getNickname($user);

            // todo 事件相应处理
            try {
                $sms       = new Sms();
                $smsConfig = AppConfigLogic::getSmsConfig();
                $sms->sendNewUserMessage($smsConfig['mobile_list'], $nickname);

                $ErrorLog = new ErrorLog();
                $ErrorLog->store('EVENT_REGISTER', '用户注册事件');

            } catch (\Exception $exception) {
                \Yii::error('注册事件');
            }
        });
    }

    public function getNickname(User $user)
    {
        if (!empty($user->nickname)) {
            $nickname = $user->nickname;
        } elseif (!empty($user->username)) {
            $nickname = $user->username;
        } elseif (!empty($user->mobile)) {
            $nickname = $user->mobile;
        } else {
            $nickname = $user->id;
        }

        return $nickname;
    }
}
