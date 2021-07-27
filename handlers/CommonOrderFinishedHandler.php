<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-22
 * Time: 9:46
 */

namespace app\handlers;


use app\component\jobs\CommonOrderFinishedJob;
use app\models\CommonOrder;

class CommonOrderFinishedHandler extends BaseHandler
{
    /**
     * 事件处理
     */
    public function register()
    {
        \Yii::$app->on(CommonOrder::COMMON_ORDER_FINISHED, function ($event) {
            $common_order_id = $event->common_order_id;
            $status = $event->status;
            $user_id = $event->user_id;
            $mall_id = $event->mall_id;
            \Yii::$app->queue->delay(1)->push(new  CommonOrderFinishedJob(['common_order_id' => $common_order_id, 'status' => $status,'user_id'=>$user_id,'mall_id'=>$mall_id]));
        });
    }
}