<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-21
 * Time: 10:26
 */

namespace app\plugins\boss\handlers;




use app\handlers\BaseHandler;
use app\plugins\boss\jobs\CheckTimerJob;


class MallInitHandler extends BaseHandler
{

    const MALL_INIT = 'mall_init';

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


        \Yii::$app->on(MallInitHandler::MALL_INIT, function ($event) {
            \Yii::warning('商城初始化---');
            \Yii::$app->queue->delay(1)->push(new CheckTimerJob(['mall' => $event->mall]));
        });
    }
}