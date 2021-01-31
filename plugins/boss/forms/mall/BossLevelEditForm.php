<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\boss\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\boss\forms\common\BossLevelCommon;
use app\plugins\boss\models\Boss;
use app\plugins\boss\models\BossLevel;

class BossLevelEditForm extends BaseModel
{

    public $id;
    public $level;
    public $name;
    public $checked_condition_keys;
    public $checked_condition_values;
    public $equal_price;
    public $boss_price;
    public $equal_price_type;
    public $price_type;
    public $is_use;
    public $is_auto_upgrade;
    public $detail;
    public $condition_type;
    public $upgrade_type_goods;
    public $upgrade_type_condition;
    public $goods_type;
    public $goods_list;
    public $goods_warehouse_ids;
    public $buy_goods_type;
    public $over_boss_price;
    public $price;
    public $is_enable;
    public $extra_price;
    public $is_extra;
    public $extra_is_limit;
    public $extra_type;
    public $extra_limit_price;



    public function rules()
    {
        return [
            [['level', 'name', 'is_enable'], 'required'],
            [['level', 'name', 'checked_condition_keys', 'checked_condition_values', 'equal_price_type', 'is_enable', 'detail', 'goods_warehouse_ids', 'goods_list'], 'trim'],
            [['level','is_extra','extra_type', 'is_enable', 'id', 'is_auto_upgrade', 'condition_type', 'upgrade_type_condition', 'upgrade_type_goods', 'goods_type', 'buy_goods_type','extra_is_limit'], 'integer'],
            [['name'], 'string'],
            [['price', 'extra_price','extra_limit_price'], 'number', 'min' => 0],
            [['is_enable'], 'default', 'value' => 0],
            [['detail'], 'string', 'max' => 80],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '等级权重',
            'name' => '等级名称',
            'goods_list' => '商品列表',
            'checked_condition_keys' => '选中的表达式key',
            'goods_warehouse_ids' => '选中的商品goods_warehouse_ids',
            'checked_condition_values' => '选中的表达式的值',
            'upgrade_type_goods' => '购物升级',
            'upgrade_type_condition' => '条件升级',
            'condition_type' => '条件升级的方式',
            'goods_type' => '购物方式',
            'is_auto_upgrade' => '是否开启自动升级',
            'buy_goods_type' => '订单结算方式',
            'extra_limit_price' => '额外奖励上限金额',
            'extra_is_limit' => '额外奖励是否上线',
            'extra_type' => '额外存在的条件 1 购物 2 条件  3 任意',
            'extra_price' => '额外奖励金额',
            'is_extra' => '是否开启额外奖励',
            'price' => '永久奖励金额',
            'detail' => '等级说明',
            'is_enable' => '是否启用'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->equal_price_type == 1) {
                if ($this->price > 100) {
                    throw new \Exception('平级奖佣金百分比不能大于100%');
                }
            }
            $level = BossLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $this->level]);
            if ($level) {
                if ($level->id != $this->id) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '选中的权重已经存在对应的记录'
                    ];
                }
            }
            $level = BossLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
            if ($level) {
                if ($level->level != $this->level) {
                    $boss = Boss::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $level->level])->exists();
                    if ($boss) {
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '当前等级下已存在股东，不可变更等级权重！'
                        ];
                    }
                }
            }
            if (!$level) {
                $level = new BossLevel();
                $level->is_delete = 0;
                $level->mall_id = \Yii::$app->mall->id;
            }
            $level->attributes = $this->attributes;
            $level->checked_condition_keys = SerializeHelper::encode($this->checked_condition_keys);
            $level->checked_condition_values = SerializeHelper::encode($this->checked_condition_values);
            $level->goods_list = SerializeHelper::encode($this->goods_list);
            $level->goods_warehouse_ids = SerializeHelper::encode($this->goods_warehouse_ids);
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
        $weights = BossLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();
        $level = BossLevel::findOne(['id' => $this->id, 'is_delete' => 0]);
        if ($level) {
            if ($level->checked_condition_values) {
                $level->checked_condition_values = SerializeHelper::decode($level->checked_condition_values);
            }
            if ($level->checked_condition_keys) {
                $level->checked_condition_keys = SerializeHelper::decode($level->checked_condition_keys);
            }
            if ($level->goods_warehouse_ids) {
                $level->goods_warehouse_ids = SerializeHelper::decode($level->goods_warehouse_ids);
            } else {
                $level->goods_warehouse_ids = [];
            }
            if ($level->goods_list) {
                $level->goods_list = SerializeHelper::decode($level->goods_list);

            } else {
                $level->goods_list = [];
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