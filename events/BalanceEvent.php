<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 余额变动事件
 * Author: xuyaoxiang
 * Date: 2020/10/13
 * Time: 16:52
 */

namespace app\events;

use app\models\BalanceLog;
use yii\base\Event;

class BalanceEvent extends Event
{
    /** @var app/models/BalanceLog */
    public $balance_log;
}
