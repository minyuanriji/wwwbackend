<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-15
 * Time: 10:42
 */

namespace app\handlers;

use app\component\jobs\ParentChangeJob;
use app\component\jobs\UserRelationUpgradeJob;
use app\events\UserInfoEvent;
use app\models\User;
use app\services\wechat\WechatTemplateService;

class RelationHandler extends BaseHandler
{
    const RELATION_CHANGE = 'relation_change'; // 用户关系链变化
    const USER_INVITER_UPDATE = 'user_inviter_update';//检测并更新用户是否是邀请者状态
    const INVITER_STATUS_CHANGE = 'inviter_status_change'; // 邀请者状态发生改变
    const CHANGE_PARENT = 'change_parent'; // 用户关系链变化

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::warning("---RelationHandler start--");
        try{
            /*
            \Yii::$app->on(RelationHandler::CHANGE_PARENT, function ($event) {
                try {
                    //启动队列去维护关系相关的表
                    \Yii::$app->queue->delay(0)->push(new ParentChangeJob([
                        'user_id'=>$event->user_id,
                        'mall_id'=>$event->mall_id,
                        'parent_id'=>$event->parent_id,
                    ]));

                    \Yii::warning('关系处理器关系改变事件');
                } catch (\Exception $exception) {
                    \Yii::error('关系处理器关系改变事件');
                }
            });

            \Yii::$app->on(RelationHandler::INVITER_STATUS_CHANGE, function ($event) {
                // todo 事件相应处理

            });

            \Yii::$app->on(RelationHandler::USER_INVITER_UPDATE, function ($event) {
                \Yii::warning('USER_INVITER_UPDATE --检测用户是否可以升级为inviter--');
                //检测用户是否可以升级为inviter
                \Yii::$app->queue->delay(0)->push(new UserRelationUpgradeJob([
                    'user_id' => $event->user_id,
                    'mall_id' => $event->mall_id
                ]));
            });
            */
        }catch (\Exception $exception){
            \Yii::error('RelationHandler_exception 出现异常'."File:".$exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage());
        }
    }
}
