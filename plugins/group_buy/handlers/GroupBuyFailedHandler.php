<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/10/12
 * Time: 17:19
 */


/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 拼团失败事件处理
 * Author: xuyaoxiang
 * Date: 2020/10/12
 * Time: 10:59
 */

namespace app\plugins\group_buy\handlers;

use app\handlers\BaseHandler;
use app\plugins\group_buy\models\PluginGroupBuyActive;

use app\plugins\group_buy\services\ActiveServices;

class GroupBuyFailedHandler extends BaseHandler
{
    public function register()
    {
        \Yii::$app->on(PluginGroupBuyActive::EVENT_GROUP_BUY_ACTIVE_FAILED, function ($event) {
            $plugin_group_buy_active = $event->plugin_group_buy_active;
            foreach ($plugin_group_buy_active->activeItems as $active_item) {
                $ActiveServices = new ActiveServices();
                $ActiveServices->sendWechatTempFailed($active_item);
            }
        });
    }
}

