<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 缓存接口类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:55
 */

namespace app\component\caches;

interface InterfaceCache
{
    public function setValue($key,$value);

    public function getValue($key);

    public function deleteValue($key);

    public function getDuration();
}