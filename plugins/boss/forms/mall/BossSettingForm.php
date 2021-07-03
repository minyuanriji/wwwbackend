<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\boss\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\boss\models\BossSetting;

class BossSettingForm extends BaseModel
{
    public $detail;//说明
    public $is_enable;
    public $compute_type;//结算方式  0、订单完成后  1、订单支付后
    public $compute_period;//结算方式  0、订单完成后  1、订单支付后
    public function rules()
    {
        return [
            [['compute_period', 'is_enable', 'compute_type'], 'integer'],
            [['detail'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_enable' => '启用股东',
            'compute_period' => '结算周期',
            'compute_type' => '结算方式',
            'detail' => '说明'

        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = ['is_enable', 'compute_period', 'compute_type'];
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
                $setting = BossSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new BossSetting();
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
        $list = BossSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
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