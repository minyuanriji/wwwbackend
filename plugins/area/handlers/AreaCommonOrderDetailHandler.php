<?php

namespace app\plugins\area\handlers;

use app\handlers\BaseHandler;
use app\handlers\CommonOrderDetailHandler;
use app\plugins\area\jobs\AreaLogJob;

class AreaCommonOrderDetailHandler extends BaseHandler
{
    /**
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.
        \Yii::$app->on(CommonOrderDetailHandler::COMMON_ORDER_DETAIL_CREATED, function ($event) {
            $common_order_detail_id = $event->common_order_detail_id;
            \Yii::$app->queue->delay(3)->push(new AreaLogJob(['common_order_detail_id' => $common_order_detail_id, 'type' => 1]));
        });
        // TODO: Implement register() method.
        \Yii::$app->on(CommonOrderDetailHandler::COMMON_ORDER_DETAIL_STATUS_CHANGED, function ($event) {
            $common_order_detail_id = $event->common_order_detail_id;
            \Yii::$app->queue->delay(3)->push(new AreaLogJob(['common_order_detail_id' => $common_order_detail_id, 'type' => 2]));
        });
    }
}