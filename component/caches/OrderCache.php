<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 订单缓存类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:55
 */

namespace app\component\caches;

class OrderCache extends BaseCache
{
    public function addOrderCache($id, $value)
    {
        return $this->setValue("OrderCache" . $id, $value,$this->duration);
    }

    public function getOrderCache($id)
    {
        return $this->getValue("OrderCache" . $id);
    }

    public function delOrderCache($id)
    {
        return $this->deleteValue("OrderCache" . $id);
    }
}
