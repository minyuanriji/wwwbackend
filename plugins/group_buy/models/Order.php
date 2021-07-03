<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/12
 * Time: 15:06
 */

namespace app\plugins\group_buy\models;

use app\models\Order as ParentOrder;

class Order extends ParentOrder
{
    //拼团订单创建事件
    const EVENT_GROUP_BUY_CREATED = 'group_buy_order_created';
    const EVENT_GROUP_BUY_CANCELED = 'group_buy_order_canceled';

    public function getActiveItem()
    {
        return $this->hasOne(PluginGroupBuyActiveItem::className(), ['order_id' => 'id']);
    }

    public function getActive()
    {
        return $this->hasOne(PluginGroupBuyActive::className(), ['id' => 'active_id'])
            ->viaTable(PluginGroupBuyActiveItem::tableName(), ['order_id' => 'id']);
    }
}