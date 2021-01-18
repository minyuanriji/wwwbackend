<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 名片缓存类
 * Author: zal
 * Date: 2020-07-22
 * Time: 10:55
 */

namespace app\plugins\business_card\cache;

use app\component\caches\BaseCache;

class BusinessCardCache extends BaseCache
{
    public $key = "_business_card_";

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
