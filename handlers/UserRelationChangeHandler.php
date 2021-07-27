<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 用戶上下级关系更改
 * Author: zal
 * Date: 2020-05-15
 * Time: 10:42
 */

namespace app\handlers;

use app\component\jobs\ChangeSuperiorJob;
use app\events\RelationChangeEvent;

class UserRelationChangeHandler extends BaseHandler
{
    const CHANGE_PARENT = 'CHANGE_PARENT'; // 用户变更上级事件

    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::warning("---UserRelationChangeHandler start--");
        try{
            \Yii::$app->on(UserRelationChangeHandler::CHANGE_PARENT, function ($event) {
                // todo 事件相应处理
                try {
                    /**
                     * @var RelationChangeEvent $event
                     */
                    \Yii::$app->queue->delay(0)->push(new ChangeSuperiorJob([
                        'mall' => $event->mall,
                        'beforeParentId' => $event->beforeParentId,
                        'parentId' => $event->parentId,
                        'user_id' => $event->userId
                    ]));
                    \Yii::warning('用户上下级关系改变事件');
                } catch (\Exception $exception) {
                    \Yii::error('用户上下级关系改变事件出现异常；file:'.$exception->getFile().",line:".$exception->getLine().",Message:".$exception->getMessage());
                }
            });
        }catch (\Exception $exception){
            \Yii::error('UserRelationChangeHandler_exception 出现异常'."File:".$exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage());
        }
    }
}
