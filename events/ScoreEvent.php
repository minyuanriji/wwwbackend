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

use app\models\ScoreLog;
use yii\base\Event;

class ScoreEvent extends Event
{
    /** @var app/models/ScoreLog */
    public $score_log;
}
