<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 分销分佣订单事件
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:10
 */

namespace app\plugins\boss\events;

use yii\base\Event;

class BossOrderEvent extends Event
{
    public $order;
    //操作类型1新增2退款3过售后4更新支付状态
    public $type = 1;
    public $order_detail_id = 0;
}