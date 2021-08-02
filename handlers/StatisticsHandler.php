<?php
/**
 * Created by PhpStorm.
 * User: 阿源
 * Date: 2020/10/21
 * Time: 17:34
 */

namespace app\handlers;

use app\events\StatisticsEvent;
use app\models\StatisticsBrowseLog;

class StatisticsHandler extends BaseHandler{


    public function register()
    {
        // TODO: Implement register() method.
        \Yii::warning("---浏览记录开始存储--");
        try{
            \Yii::$app->on(StatisticsBrowseLog::EVEN_STATISTICS_LOG, function ($event) {
                // todo 事件相应处理

                /** @var StatisticsEvent $event */
                $log = new StatisticsBrowseLog();
                if (empty($event->user_id)){
                    $log->type = 1;
                    $log->user_ip = $event->user_ip;
                }else{
                    $log->type = 0;
                    $log->user_id = $event->user_id;
                }
                $log->created_at = time();
                $log->mall_id = $event->mall_id;
                $log->browse_type = $event->browse_type;
                if (!$log->save()) \Yii::warning("---存储失败--");

            });
        }catch (\Exception $exception){
            \Yii::error('RelationHandler_exception 出现异常'."File:".$exception->getFile().";Line:".$exception->getLine().";message:".$exception->getMessage());
        }
    }
}