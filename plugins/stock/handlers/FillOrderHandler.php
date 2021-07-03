<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金处理
 * Author: zal
 * Date: 2020-05-21
 * Time: 20:27
 */

namespace app\plugins\stock\handlers;

use app\handlers\BaseHandler;
use app\plugins\stock\jobs\AgentOrderJob;
use app\plugins\stock\jobs\FillOrderPayedJob;

class FillOrderHandler extends BaseHandler
{
    const ORDER_PAID = 'order_paid';
    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method
        \Yii::$app->on(FillOrderHandler::ORDER_PAID, function ($event) {

            \Yii::warning('触发订单事件---------------');

            \Yii::$app->queue->delay(0)->push(new FillOrderPayedJob([
                'order' => $event->order
            ]));
        });
    }
}