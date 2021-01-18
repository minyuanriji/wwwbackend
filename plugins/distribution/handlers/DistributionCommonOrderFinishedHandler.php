<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 10:10
 */

namespace app\plugins\distribution\handlers;


use app\handlers\BaseHandler;
use app\models\CommonOrder;
use app\plugins\distribution\jobs\DistributionCommonOrderFinishedJob;

class DistributionCommonOrderFinishedHandler extends BaseHandler
{

    public $common_order_id;
    public $status;


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

        \Yii::$app->on(CommonOrder::COMMON_ORDER_FINISHED, function ($event) {
            $common_order_id = $event->common_order_id;
            $status = $event->status;
            \Yii::$app->queue->delay(0)->push(new DistributionCommonOrderFinishedJob(['common_order_id' => $common_order_id, 'status' => $status]));
        });
    }
}