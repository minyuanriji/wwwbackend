<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-07-30
 * Time: 15:20
 */

namespace app\plugins\stock\handlers;


use app\handlers\BaseHandler;

class StockOrderPayedHandler extends BaseHandler
{

    const  STOCK_ORDER_PAYED = 'stock_order_payed';

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

        \Yii::$app->on(StockOrderPayedHandler::STOCK_ORDER_PAYED, function ($event) {

        });


    }
}