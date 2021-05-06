<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金处理
 * Author: zal
 * Date: 2020-05-21
 * Time: 20:27
 */

namespace app\plugins\boss\handlers;

use app\handlers\BaseHandler;
use app\plugins\boss\jobs\BossOrderJob;

class BossOrderHandler extends BaseHandler
{

    const DISTRIBUTION_ORDER = 'DISTRIBUTION_ORDER';

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
        \Yii::$app->on(BossOrderHandler::DISTRIBUTION_ORDER, function ($event) {
            \Yii::$app->queue->delay(0)->push(new BossOrderJob([
                'order' => $event->order,
                'type' => $event->type,
                'order_detail_id' => $event->order_detail_id
            ]));
        });
    }
}