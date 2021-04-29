<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 佣金事件
 * Author: zal
 * Date: 2020-05-25
 * Time: 17:10
 */

namespace app\plugins\distribution\events;

use yii\base\Event;

class DistributionCommissionEvent extends Event
{
    public $order;
    public $mall;
    public $distributionOrderList;
    public $type;
}