<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-02
 * Time: 12:58
 */

namespace app\handlers;



class TestHandler extends BaseHandler
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

        \Yii::$app->on('order_created',function ($event){
            $cacheKey = 'order_id';
            \Yii::$app->cache->set($cacheKey,'1',10);
            echo'注册事件';

        });
    }
}