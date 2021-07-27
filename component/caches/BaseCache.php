<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 基础缓存类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:35
 */

namespace app\component\caches;

use yii;

class BaseCache implements InterfaceCache
{

    /** @var int 有效期 单位秒 */
    public $duration = 3600;

    protected $cache;

    protected $prefix = "jxmall_v1_";

    public function __construct()
    {
        $this->cache = Yii::$app->cache;
    }

    /**
     * 设置键值对缓存数据
     * @param $key
     * @param $value
     * @return bool
     */
    public function setValue($key, $value)
    {
        $duration = $this->duration;
        // TODO: Implement setValue() method.
        if ($this->cache)
            return $this->cache->set($this->prefix.$key, $value,$duration);
        return false;
    }

    /**
     * 获取键值
     * @param $key
     * @return bool
     */
    public function getValue($key)
    {
        if (isset($_GET['noCache']) && $_GET['noCache']=='y')
            return false;
        // TODO: Implement getValue() method.
        if ($this->cache)
            return $this->cache->get($this->prefix.$key);
        return false;
    }

    /**
     * 删除键
     * @param $key
     * @return bool
     */
    public function deleteValue($key)
    {
        // TODO: Implement deleteValue() method.
        if ($this->cache)
            return $this->cache->delete($this->prefix.$key);
        return false;
    }

    /**
     * 获取有效期
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

}