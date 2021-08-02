<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\stock\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\stock\forms\common\StockLevelCommon;
use app\plugins\stock\models\Stock;
use app\plugins\stock\models\StockAgent;
use app\plugins\stock\models\StockLevel;

class StockLevelEditForm extends BaseModel
{
    public $id;
    public $level;
    public $name;
    public $checked_condition_keys;
    public $checked_condition_values;
    public $is_use;
    public $is_auto_upgrade;
    public $detail;
    public $condition_type;
    public $upgrade_type_condition;
    public $service_price;
    public $service_price_type;
    public $sub_stock_rate;
    public $is_equal;
    public $is_over;
    public $is_fill;

    public function rules()
    {
        return [
            [['level', 'name', 'is_use'], 'required'],
            [['level', 'name', 'checked_condition_keys', 'checked_condition_values', 'is_use', 'detail'], 'trim'],
            [['level', 'service_price_type', 'is_fill', 'is_over', 'is_equal', 'is_use', 'id', 'is_auto_upgrade', 'condition_type', 'upgrade_type_condition', 'sub_stock_rate'], 'integer'],
            [['name'], 'string'],
            [['service_price'], 'number', 'min' => 0],
            [['is_auto_upgrade'], 'default', 'value' => 1],
            [['is_use'], 'default', 'value' => 0],
            [['detail'], 'string', 'max' => 80],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '分销商等级',
            'name' => '分销商等级名称',
            'detail' => '等级说明',
            'condition_type' => '条件升级类型',
            'upgrade_type_condition' => '开启条件升级',
            'stock_price_type' => '代理商佣金类型',
            'buy_goods_type' => '结算状态',
            'service_price_type' => '代理商服务费类型',
            'service_price' => '代理商服务费',
            'sub_stock_rate' => '减库存比例',
            'is_equal'=>'平级',
            'is_fill'=>'补货奖',
            'is_over'=>'越级奖励'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $level = StockLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $this->level]);
            if ($level) {
                if ($level->id != $this->id) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '选中的权重已经存在对应的记录'
                    ];
                }
            }
            $level = StockLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
            if ($level) {
                if ($level->level != $this->level) {
                    $agent = StockAgent::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $level->level])->exists();
                    if ($agent) {
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '当前等级下已存在分销商，不可变更等级权重！'
                        ];
                    }
                }
            }
            if (!$level) {
                $level = new StockLevel();
                $level->is_delete = 0;
                $level->mall_id = \Yii::$app->mall->id;
            }
            $level->attributes = $this->attributes;
            $level->checked_condition_keys = SerializeHelper::encode($this->checked_condition_keys);
            $level->checked_condition_values = SerializeHelper::encode($this->checked_condition_values);
            if (!$level->save()) {
                throw new \Exception($this->responseErrorMsg($level));
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }
        } catch (\Exception $exception) {
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function getDetail()
    {
        $weights = StockLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();
        $level = StockLevel::findOne(['id' => $this->id, 'is_delete' => 0]);
        if ($level) {
            if ($level->checked_condition_values) {
                $level->checked_condition_values = SerializeHelper::decode($level->checked_condition_values);
            }
            if ($level->checked_condition_keys) {
                $level->checked_condition_keys = SerializeHelper::decode($level->checked_condition_keys);
            }
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '',
                'data' => [
                    'detail' => $level,
                    'weights' => $weights
                ]
            ];
        }
    }
}