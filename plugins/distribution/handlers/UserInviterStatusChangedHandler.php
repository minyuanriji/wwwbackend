<?php

namespace app\plugins\distribution\handlers;

use app\handlers\BaseHandler;
use app\handlers\RelationHandler;
use app\models\User;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionSetting;

class UserInviterStatusChangedHandler extends BaseHandler
{

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(RelationHandler::INVITER_STATUS_CHANGE, function ($event) {
            // todo 事件相应处理
            $user = User::findOne($event->user_id);
            $level = DistributionSetting::getValueByKey('level', $user->mall_id);
            if ($event->is_inviter) {
                if ($level) {
                    $distribution = Distribution::findOne(['user_id' => $user->id]);
                    if (!$distribution) {
                        $distribution = new Distribution();
                        $distribution->user_id = $user->id;
                        $distribution->mall_id = $user->mall_id;
                        $distribution->save();
                    }
                }
            } else {
                $distribution = Distribution::findOne(['user_id' => $user->id, 'is_delete'=>0]);
                if ($distribution) {
                    $distribution->is_delete = 1;
                    $distribution->delete_reason = '推广资格被取消';
                    $distribution->save();
                }
            }
        });
    }
}
