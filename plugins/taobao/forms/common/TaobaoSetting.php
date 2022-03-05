<?php

namespace app\plugins\taobao\forms\common;

use app\models\BaseModel;
use app\models\Option;

class TaobaoSetting extends BaseModel{

    /**
     * 获取所有配置
     * @param $mall_id
     * @return array
     */
    public static function all($mall_id){
        $options = Option::find()->where([
            "mall_id"  => $mall_id,
            "mch_id"   => 0,
            "group"    => Option::GROUP_TAOBAO
        ])->all();
        $settings = [];
        if($options){
            foreach($options as $option){
                $settings[$option->name] = $option->value;
            }
        }

        return $settings ? $settings : '';
    }

    /**
     * 获取一个配置值
     * @param $name
     * @param $mall_id
     * @return Option|null
     */
    public static function get($name, $mall_id){
        return Option::findOne([
            "mall_id"  => $mall_id,
            "mch_id"   => 0,
            "group"    => Option::GROUP_TAOBAO,
            "name"     => $name
        ]);
    }

    /**
     * 设置一个配置值
     * @param $name
     * @param $value
     * @param $mall_id
     * @throws \Exception
     */
    public static function set($name, $value, $mall_id){
        $option = static::get($name, $mall_id);
        if(!$option){
            $option = new Option([
                "mall_id"    => $mall_id,
                "mch_id"     => 0,
                "group"      => Option::GROUP_TAOBAO,
                "name"       => $name,
                "created_at" => time()
            ]);
        }
        $option->value      = !empty($value) ? $value : "-";
        $option->updated_at = time();
        if(!$option->save()){
            $errors = $option->getFirstErrors();
            throw new \Exception(array_shift($errors));
        }
    }
}