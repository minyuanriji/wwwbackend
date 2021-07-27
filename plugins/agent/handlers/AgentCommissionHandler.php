<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销佣金处理
 * Author: zal
 * Date: 2020-05-21
 * Time: 20:27
 */

namespace app\plugins\agent\handlers;

use app\handlers\BaseHandler;
use app\plugins\agent\jobs\AgentCommissionJob;
use app\plugins\agent\jobs\AgentInsertJob;


class AgentCommissionHandler extends BaseHandler
{

    const DISTRIBUTION_COMMISSION = 'DISTRIBUTION_COMMISSION';

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-02
     * @Time: 12:57
     * @Note:所有的事件都要通过此方法进去注册
     * @return mixed
     */
    public function register()
    {
        // TODO: Implement register() method.
        \Yii::$app->on(AgentCommissionHandler::DISTRIBUTION_COMMISSION, function ($event) {
            \Yii::$app->queue->delay(0)->push(new AgentCommissionJob([
                'mall' => $event->mall,
                'order' => $event->order,
                'agentOrderList' => $event->agentOrderList,
                'type' => $event->type
            ]));
        });
    }
}