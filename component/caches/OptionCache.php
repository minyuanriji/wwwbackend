<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 网站配置缓存类
 * Author: zal
 * Date: 2020-04-24
 * Time: 16:55
 */

namespace app\component\caches;


class OptionCache extends BaseCache
{

    public $duration = 0;

    private $key = "option_cache_";

    /** @var string 名称 */
    public $name;

    /** @var int 商城id */
    public $mall_id = 0;

    /** @var string 组名 api|admin */
    public $group = '';

    /** @var null 默认值 */
    public $default = null;

    /** @var int 商户id */
    public $mch_id = 0;

    public function __construct($name,$mall_id,$group,$default,$mch_id)
    {
        $this->name = $name;
        $this->mall_id = $mall_id;
        $this->group = $group;
        $this->default = $default;
        $this->mch_id = $mch_id;
        $this->key = $this->key.$this->name."_".$this->mall_id."_".$this->group."_".$this->mch_id;
        parent::__construct();
    }

    public function setCache($value)
    {
        return $this->setValue($this->key, $value);
    }

    public function getCache()
    {
        return $this->getValue($this->key);
    }

    public function delCache()
    {
        return $this->deleteValue($this->key);
    }
}