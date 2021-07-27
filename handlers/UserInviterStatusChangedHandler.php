<?php

namespace app\handlers;


use app\events\UserEvent;
use app\forms\common\coupon\CommonCouponAutoSend;
use app\forms\common\coupon\CouponAutoSendCommon;

class UserInviterStatusChangedHandler extends BaseHandler
{

    const USER_INVITER_STATUS_CHANGED='user_inviter_status_changed';


    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(UserInviterStatusChangedHandler::USER_INVITER_STATUS_CHANGED, function ($event) {
            // todo 事件相应处理

        });
    }
}
