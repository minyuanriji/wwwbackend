<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 购物车事件
 * Author: zal
 * Date: 2020-04-17
 * Time: 14:19
 */

namespace app\events;

use app\models\Order;
use yii\base\Event;

class CartEvent extends Event
{
    /** @var Order */
    public $cartIds;
}
