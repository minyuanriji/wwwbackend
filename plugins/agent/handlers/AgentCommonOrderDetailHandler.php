<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-02
 * Time: 15:32
 */

namespace app\plugins\agent\handlers;


use app\handlers\BaseHandler;
use app\handlers\CommonOrderDetailHandler;
use app\plugins\agent\jobs\AgentLogJob;

class AgentCommonOrderDetailHandler extends BaseHandler
{

    /**
     * @Author: 广东七件事 ganxiaohao
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.
        \Yii::$app->on(CommonOrderDetailHandler::COMMON_ORDER_DETAIL_CREATED, function ($event) {
            $common_order_detail_id = $event->common_order_detail_id;
            \Yii::$app->queue->delay(5)->push(new AgentLogJob(['common_order_detail_id' => $common_order_detail_id, 'type' => 1]));
        });
        // TODO: Implement register() method.
        \Yii::$app->on(CommonOrderDetailHandler::COMMON_ORDER_DETAIL_STATUS_CHANGED, function ($event) {
            $common_order_detail_id = $event->common_order_detail_id;
            \Yii::$app->queue->delay(5)->push(new AgentLogJob(['common_order_detail_id' => $common_order_detail_id, 'type' => 2]));
        });
    }
}