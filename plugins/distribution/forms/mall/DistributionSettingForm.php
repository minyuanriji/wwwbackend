<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 16:30
 */

namespace app\plugins\distribution\forms\mall;


use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\plugins\distribution\models\DistributionSetting;

class DistributionSettingForm extends BaseModel
{
    public $level;
    public $is_self_buy;
    public $price_type;
    public $first_price;
    public $second_price;
    public $third_price;
    public $is_show_share_level;
    public $is_apply;
    public $is_check;
    public $rebuy_price_date;
    public $is_rebuy;
    public $is_team;
    public $subsidy_price_date;
    public $is_subsidy;
    public $protocol;//申请协议


    public function rules()
    {
        return [
            [['level', 'is_self_buy', 'price_type'], 'required'],
            [['protocol'],'string'],
            [['level', 'is_self_buy', 'price_type', 'is_show_share_level', 'is_apply', 'is_check', 'rebuy_price_date', 'is_rebuy', 'is_team', 'is_subsidy', 'subsidy_price_date'], 'integer'],
            [['first_price', 'second_price', 'third_price',], 'number', 'min' => 0],
            [['first_price', 'second_price', 'third_price'], function ($attr, $params) {
                switch ($this->level) {
                    case 3:
                        if ($this->third_price == '') {
                            $this->addError($attr, '请输入三级分销佣金');
                        }
                        if ($this->second_price == '') {
                            $this->addError($attr, '请输入二级分销佣金');
                        }
                        if ($this->first_price == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    case 2:
                        if ($this->second_price == '') {
                            $this->addError($attr, '请输入二级分销佣金');
                        }
                        if ($this->first_price == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    case 1:
                        if ($this->first_price == '') {
                            $this->addError($attr, '请输入一级分销佣金');
                        }
                        break;
                    default:
                        break;
                }
            }, 'skipOnEmpty' => false, 'skipOnError' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '分销层级',
            'is_self_buy' => '分销内购',
            'price_type' => '分销佣金类型',
            'first_price' => '一级佣金',
            'second_price' => '二级佣金',
            'third_price' => '三级佣金',
            'is_check' => '是否需要审核',
            'is_apply' => '需要申请',
            'rebuy_price_date' => '复购结算日期',
            'is_team' => '是否启用团队复购奖励',
            'subsidy_price_date' => '补贴奖励发放日期',
            'is_subsidy' => '开启补贴奖励',
            'protocol' => '申请协议'
        ];
    }


    //  subsidy_price_date:1,
    //                    is_subsidy:0,

    public static function strToNumber($key, $str)
    {
        $default = [
            'level', 'is_self_buy', 'price_type',
            'first_price', 'second_price', 'third_price', 'is_show_share_level', 'is_check', 'is_apply', 'is_rebuy', 'is_team','subsidy_price_date','is_subsidy','protocol'
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
                $setting = DistributionSetting::findOne(['mall_id' => \Yii::$app->mall->id, 'key' => $item['key']]);
                if (!$setting) {
                    $setting = new DistributionSetting();
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
        $list = DistributionSetting::find()->where(['mall_id' => \Yii::$app->mall->id, 'is_delete' => 0])->asArray()->all();
        $newItem = [];
        foreach ($list as $item) {
            if ($item['key'] == 'pay_type') {
                $item['value'] = SerializeHelper::decode($item['value']);
            }
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

    public function getGoodsDistributionConfig()
    {
        $level = DistributionSetting::get(\Yii::$app->mall->id, DistributionSetting::LEVEL);
        $shareArray = [
            [
                'label' => '一级分销',
                'value' => 'distribution_commission_first',
            ],
            [
                'label' => '二级分销',
                'value' => 'distribution_commission_second',
            ],
            [
                'label' => '三级分销',
                'value' => 'distribution_commission_third',
            ],
        ];
        array_splice($shareArray, $level);
        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '',
            'data' => [
                'shareArray' => $shareArray,
                'distributionLevelList' => DistributionLevelCommon::getInstance()->getList(),
            ]
        ];
    }


}