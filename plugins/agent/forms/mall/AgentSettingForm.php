<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\agent\forms\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\agent\models\AgentSetting;

class AgentSettingForm extends BaseModel
{
    public $is_self_buy;
    public $upgrade_type;
    public $is_equal;
    public $is_enable;
    public $is_contain_self;
    public $equal_level;//平级层数
    public $agent_level;//奖励层数
    public $compute_type;//结算方式  0、订单完成后  1、订单支付后
    public $over_level;//越级层数
    public $is_equal_self;//平级奖从消费者自身算起

    public function rules()
    {
        return [
            [['is_contain_self', 'is_enable', 'is_self_buy', 'is_equal', 'over_level', 'compute_type', 'agent_level', 'equal_level','is_equal_self'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_enable' => '启用经销商',
            'is_contain_self' => '团队包含自己',
            'is_self_buy' => '开启内购',
            'is_equal' => '启用平级',
            'equal_level' => '平级层数',
            'agent_level' => '奖励层数',
            'over_level' => '越级层数',
            'compute_type' => '结算方式',
            'is_equal_self'=>'平级奖从消费者自身算起'
        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = [
            'is_enable', 'is_self_buy', 'is_contain_self', 'is_equal','is_equal_self','compute_type','over_level','equal_level','agent_level'
        ];
        if (in_array($key, $default)) {
            return round($str, 2);
        }
        return $str;
    }


    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $setting_list = [];
            foreach ($this->attributes as $index => $item) {
                $setting_list[] = [
                    'key' => $index,
                    'value' => $item
                ];
            }
            foreach ($setting_list as $item) {
                $setting = AgentSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new AgentSetting();
                }
                $setting->key = $item['key'];
                $setting->value = $item['value'];
                $setting->mall_id = \Yii::$app->mall->id;
                $setting->is_delete = 0;
                $setting->save();
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功'
            ];
        } catch (\Exception $e) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function search()
    {
        $list = AgentSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        $newItem = [];
        foreach ($list as $item) {

            $newItem[$item['key']] = self::strToNumber($item['key'], $item['value']);
        }
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'setting' => $newItem
            ]
        ];
    }

}