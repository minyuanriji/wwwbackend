<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 商品缓存类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:55
 */

namespace app\component\caches;

class GoodsCache extends BaseCache
{
    public function addCache($id, $value)
    {
        return $this->setValue("GoodsCache".$id, $value);
    }

    public function getCache($id)
    {
        return $this->getValue("GoodsCache".$id);
    }

    public function delCache($id)
    {
        return $this->deleteValue("GoodsCache".$id);
    }
}