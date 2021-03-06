<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-21
 * Time: 20:27
 */

namespace app\plugins\distribution\handlers;

use app\handlers\BaseHandler;
use app\plugins\distribution\jobs\DistributionInsertJob;


class DistributionInsertHandler extends BaseHandler
{

    const DISTRIBUTION_INSERT = 'DISTRIBUTION_INSERT';

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
        \Yii::$app->on(DistributionInsertHandler::DISTRIBUTION_INSERT, function ($event) {
            \Yii::$app->queue->delay(0)->push(new DistributionInsertJob(['user_id' => $event->user_id, 'mall_id' => $event->mall_id]));
        });
    }
}