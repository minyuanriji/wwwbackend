<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\stock\forms\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\stock\models\StockSetting;

class StockSettingForm extends BaseModel
{
    public $is_enable;
    public $equal_level;//平级层数

    public $is_allow_temp_fill;
    public $temp_fill_time;//补货时间
    public $compute_time; //结算时间
    public $fill_sms; //补货提醒

    public function rules()
    {
        return [
            [['temp_fill_time','compute_time'], 'number'],
            [['is_allow_temp_fill', 'is_enable', 'equal_level'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_enable' => '启用代理商',
            'equal_level' => '平级层数',
            'is_allow_temp_fill' => '允许临时补货',
            'temp_fill_time' => '补货限时',
            'compute_time'=>'结算时间',
            'fill_sms'=>'补货提醒短信通知'
        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = [
            'is_enable', 'is_allow_temp_fill', 'is_self_buy', 'is_contain_self', 'compute_type', 'equal_level', 'agent_level'
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
            $fillData = $this->fill_sms;
            foreach ($setting_list as $item) {
                $setting = StockSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new StockSetting();
                }
                $setting->key = $item['key'];
                $setting->value = $item["key"] == "fill_sms" ? SerializeHelper::encode($fillData) : $item['value'];
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
        $list = StockSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        $newItem = [];
        foreach ($list as $item) {
            if($item['key'] == "fill_sms"){
                $newItem[$item['key']] = SerializeHelper::decode($item["value"]);
            }else{
                $newItem[$item['key']] = self::strToNumber($item['key'], $item['value']);
            }
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