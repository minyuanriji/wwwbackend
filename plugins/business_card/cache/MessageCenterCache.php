<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 消息中心缓存类
 * Author: zal
 * Date: 2020-07-27
 * Time: 10:55
 */

namespace app\plugins\business_card\cache;

use app\component\caches\BaseCache;

class MessageCenterCache extends BaseCache
{
    public $key = "_message_center_";

    public function set($id, $value)
    {
        return $this->setValue($this->key . $id, $value,$this->duration);
    }

    public function get($id)
    {
        return $this->getValue($this->key . $id);
    }

    public function del($id)
    {
        return $this->deleteValue($this->key . $id);
    }
}
