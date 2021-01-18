<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼团事件
 * Author: xuyaoxiang
 * Date: 2020/9/18
 * Time: 16:35
 */

namespace app\plugins\group_buy\event;

use app\plugins\group_buy\models\PluginGroupBuyActive;
use yii\base\Event;

class ActiveEvent extends Event
{
    //app\plugins\group_buy\models\PluginGroupBuyActive
    public  $plugin_group_buy_active;
}

