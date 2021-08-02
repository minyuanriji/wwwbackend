<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单取消
 * Author: zal
 * Date: 2020-04-21
 * Time: 15:16
 */

namespace app\handlers\orderHandler;

class OrderCanceledHandlerClass extends BaseOrderCanceledHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;

        $this->cancel();
    }
}
