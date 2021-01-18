<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 网站配置业务处理类
 * Author: zal
 * Date: 2020-04-06
 * Time: 17:36
 */

namespace app\logic;

use app\component\caches\OptionCache;
use app\helpers\SerializeHelper;
use app\models\Option;

class OptionLogic extends BaseLogic
{
    private static $loadedOptions = [];

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 设置
     * @param $name string Name
     * @param $value mixed Value
     * @param $mall_id integer Integer
     * @param $group string Name
     * @param $mch_id integer Name
     * @return boolean
     */
    public static function set($name, $value, $mall_id = 0, $group = '', $mch_id = 0)
    {
        if (empty($name)) {
            return false;
        }
        $model = Option::findOne([
            'name' => $name,
            'mall_id' => $mall_id,
            'group' => $group,
            'mch_id' => $mch_id,
        ]);
        if (!$model) {
            $model = new Option();
            $model->name = $name;
            $model->mall_id = $mall_id;
            $model->group = $group;
            $model->mch_id = $mch_id;
        }
        $model->value = \Yii::$app->serializer->encode($value);
        $result = $model->save();
        if ($result) {
            $loadedOptionKey = md5(json_encode([
                'name' => $name,
                'mall_id' => $mall_id,
                'group' => $group,
                'mch_id' => $mch_id,
            ]));
            $optionCache = self::getOptionCacheClass($name,$mall_id,$group,null,$mch_id);
            $optionCache->delCache();
            $optionCache->setCache($model->value);
            self::$loadedOptions[$loadedOptionKey] = $value;
        }
        return $result;
    }

    /**
     * 获取
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @param $name string Name
     * @param $mall_id integer Integer
     * @param $mch_id integer Integer
     * @param $group string Name
     * @param $default string Name
     * @return mixed
     */
    public static function get($name, $mall_id = 0, $group='', $default = null, $mch_id = 0)
    {

        $loadedOptionKey = md5(json_encode([
            'name' => $name,
            'mall_id' => $mall_id,
            'group' => $group,
            'mch_id' => $mch_id,
        ]));

        if (array_key_exists($loadedOptionKey, self::$loadedOptions)) {
            return self::$loadedOptions[$loadedOptionKey];
        }
        $optionCache = self::getOptionCacheClass($name,$mall_id,$group,$default,$mch_id);
        $result = null;
        if($result=='null'||empty($result)){
            $model = Option::findOne([
                'name' => $name,
                'mall_id' => $mall_id,
                'mch_id' => $mch_id,
                'group' => $group
            ]);

            if (!$model) {
                $result = $default;
            } else {
                $result = $model->value;
                $result = SerializeHelper::decode($result);
            }
            $optionCache->setCache(SerializeHelper::encode($result));
        }else{
            $result = SerializeHelper::decode($result);
        }
        self::$loadedOptions[$loadedOptionKey] = $result;
        return $result;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note:数据保存
     * @param $list
     * @param int $mall_id
     * @param int $mch_id
     * @param string $group
     * @return bool
     */
    public static function setList($list, $mall_id = 0, $group = '', $mch_id = 0)
    {
        if (!is_array($list)) {
            return false;
        }
        foreach ($list as $item) {
            self::set(
                $item['name'],
                $item['value'],
                (isset($item['mall_id']) ? $item['mall_id'] : $mall_id),
                (isset($item['mch_id']) ? $item['mch_id'] : $mch_id),
                (isset($item['group']) ? $item['group'] : $group)
            );
        }
        return true;
    }

    /**
     * @Author: 广东七件事 zal
     * @Date: 2020-04-08
     * @Time: 19:49
     * @Note: 获取列表
     * @param $names
     * @param int $mall_id
     * @param int $mch_id
     * @param string $group
     * @param null $default
     * @return array
     */
    public static function getList($names, $mall_id = 0, $group = '', $default = null, $mch_id = 0)
    {
        if (is_string($names)) {
            $names = explode(',', $names);
        }
        if (!is_array($names)) {
            return [];
        }
        $list = [];
        foreach ($names as $name) {
            if (empty($name)) {
                continue;
            }
            $value = self::get($name, $mall_id, $group, $default, $mch_id);
            $list[$name] = $value;
        }
        return $list;
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    public function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = self::check($list[$key], $value);
            }
        }
        return $list;
    }

    /**
     * 保存
     * @param array $default
     * @return array
     */
    public function saveEnd(array $default)
    {
        foreach ($default as $k => $i) {
            foreach ($i as $k1 => $i1) {
                if (in_array($k1, ['width', 'height', 'size', 'top', 'left'])) {
                    $default[$k][$k1] = (float)$default[$k][$k1] / 2;
                }
            }
        }
        return $default;
    }

    /**
     * 海报
     * @param $list
     * @param array $default
     * @return mixed
     */
    public function poster($list, $default = [])
    {
        $new_list = $this->check($list, $default);
        $check = ['width', 'height', 'size', 'top', 'left'];
        $checkArr = ['size', 'top', 'left', 'width', 'height', 'font', 'is_show', 'type'];
        // 将个别字段转为INT类型
        foreach ($new_list as $k => $posterItem) {
            foreach ($posterItem as $checkItemKey => $checkItem) {
                if (in_array($checkItemKey, $checkArr)) {
                    $new_list[$k][$checkItemKey] = (int)$posterItem[$checkItemKey];
                }
                if (in_array($checkItemKey, $check)) {
                    $new_list[$k][$checkItemKey] = $new_list[$k][$checkItemKey] * 2;
                }
            }
        }
        return $new_list;
    }

    /**
     * 获取配置缓存类
     * @param $name
     * @param $mall_id
     * @param $group
     * @param $default
     * @param $mch_id
     * @return OptionCache
     */
    private static function getOptionCacheClass($name,$mall_id,$group,$default,$mch_id){
        return new OptionCache($name,$mall_id,$group,$default,$mch_id);
    }

    /**
     * 获取充值配置
     * @return array|\ArrayObject|mixed
     */
    public static function getRechargeSetting(){
        $data = $returnData = [];
        $res = self::get(Option::NAME_PAYMENT, \Yii::$app->mall->id, Option::GROUP_APP);
        if(!$res["balance_charge_status"]){
            return [];
        }
        if(isset($res["recharge"]) && !empty($res["recharge"])){
            if(!is_array($res["recharge"])){
                $data = SerializeHelper::decode($res['recharge']);
            }else{
                $data = $res["recharge"];
            }
        }
        if(!empty($data)){
            $i = $j = 1;
            foreach ($data as $k => $v){
                if($i%2 == 0){
                    $returnData[$j-1]["recharge_money"] = $data["recharge_money".($j)];
                    $returnData[$j-1]["give_money"] = $data["give_money".($j)];
                    $j++;
                }
                $i++;
            }
        }
        return ["list" => $returnData];
    }
}
