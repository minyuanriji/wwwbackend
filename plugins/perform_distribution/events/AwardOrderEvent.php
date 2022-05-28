<?php

namespace app\plugins\perform_distribution\events;

use yii\base\Event;

class AwardOrderEvent extends Event
{
    const SHOP_ORDER_PAID = 'perform_distribution::shop_order_paid';

    public $order;
}