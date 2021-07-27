<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 收益变动事件
 * Author: xuyaoxiang
 * Date: 2020/10/14
 * Time: 16:52
 */

namespace app\events;

use app\models\IncomeLog;
use yii\base\Event;

class IncomeEvent extends Event
{
    /** @var \app\models\IncomeLog */
    public $income_log;
}
