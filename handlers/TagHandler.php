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
use app\component\jobs\TagJob;
use app\component\jobs\UserRelationUpgradeJob;
use app\events\TagEvent;
use app\events\UserInfoEvent;
use app\logic\CommonLogic;

class TagHandler extends BaseHandler
{
    const ADD_TAG = 'add_tag'; //添加标签

    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::warning("---TagHandler start--");
        try{
            \Yii::$app->on(TagHandler::ADD_TAG, function ($event) {
                // todo 事件相应处理
                /** @var TagEvent $event */
                /*\Yii::$app->queue->delay(0)->push(new TagJob([
                    'user_id' => $event->user_id,
                    'mall_id' => $event->mall_id,
                    'cat_id' => $event->cat_id,
                    'type' => $event->type,
                    'action' => $event->action,
                ]));
                \Yii::warning('标签任务处理事件');*/
            });
        }catch (\Exception $exception){
            \Yii::error('TagHandler_exception 出现异常'.CommonLogic::getExceptionMessage($exception));
        }
    }

}