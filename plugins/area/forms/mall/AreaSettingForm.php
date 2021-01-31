<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\area\forms\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\area\models\AreaSetting;

class AreaSettingForm extends BaseModel
{
    public $is_apply;//是否需要申请
    public $is_check;//是否需要审核
    public $is_equal;//是否平均分
    public $is_enable;//是否启用
    public $is_level;//是否走极差
    public $province_price;//省代
    public $city_price;//市代
    public $district_price;//区代
    public $town_price;//镇代
    public $compute_type;//结算方式  0、订单完成后  1、订单支付后
    public $protocol;//申请协议


    public function rules()
    {
        return [
            [['is_apply', 'is_enable', 'is_check', 'is_equal', 'is_level', 'compute_type'], 'integer'],
            [['province_price', 'city_price', 'district_price', 'town_price'], 'number'],
            [['protocol'],'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'is_enable' => '启用区域分红',
            'is_apply' => '是否申请',
            'is_check' => '是否审核',
            'is_equal' => '是否平均分',
            'is_level' => '极差',
            'compute_type' => '结算方式',
            'province_price' => '省代',
            'city_price' => '市代',
            'district_price' => '区代',
            'town_price' => '镇代',
            'protocol'=>'申请协议'
        ];
    }

    public static function strToNumber($key, $str)
    {
        $default = [
            'is_apply', 'is_enable', 'is_check', 'is_equal', 'is_level', 'compute_type', 'province_price', 'city_price', 'town_price', 'district_price', 'town_price','protocol'
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
                $setting = AreaSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new AreaSetting();
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
        $list = AreaSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
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