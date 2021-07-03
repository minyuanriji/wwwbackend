<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单创建
 * Author: zal
 * Date: 2020-04-16
 * Time: 15:09
 */

namespace app\handlers\orderHandler;

use app\logic\CommonLogic;

class OrderCreatedHandlerClass extends BaseOrderCreatedHandler
{
    public function handle()
    {
        $this->user = $this->event->order->user;
        $this->setAutoCancel()->setPrint()->deleteCartGoods();

        if ('group_buy' != $this->event->order->sign) {
            $this->sendWechatTemp();
        }
    }
}
