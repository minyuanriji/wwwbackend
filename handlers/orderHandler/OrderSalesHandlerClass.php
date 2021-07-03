<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单售后
 * Author: zal
 * Date: 2020-05-18
 * Time: 11:10
 */

namespace app\handlers\orderHandler;

class OrderSalesHandlerClass extends BaseOrderSalesHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->sales();
    }
}
