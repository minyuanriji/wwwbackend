<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\distribution\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\distribution\forms\common\DistributionLevelCommon;
use app\plugins\distribution\models\Distribution;
use app\plugins\distribution\models\DistributionLevel;

class DistributionLevelEditForm extends BaseModel
{
    public $id;
    public $level;
    public $name;
    public $checked_condition_keys;
    public $checked_condition_values;
    public $price_type;
    public $first_price;
    public $second_price;
    public $third_price;
    public $is_use;
    public $is_auto_upgrade;
    public $detail;
    public $condition_type;
    public $upgrade_type_goods;
    public $upgrade_type_condition;
    public $goods_type;
    public $goods_list;
    public $goods_warehouse_ids;


    public function rules()
    {
        return [
            [['level', 'name', 'price_type', 'first_price', 'is_use'], 'required'],
            [['level', 'name', 'checked_condition_keys', 'checked_condition_values', 'price_type', 'first_price', 'is_use', 'detail', 'goods_warehouse_ids', 'goods_list'], 'trim'],
            [['level', 'price_type', 'is_use', 'id', 'is_auto_upgrade', 'condition_type', 'upgrade_type_condition', 'upgrade_type_goods', 'goods_type'], 'integer'],
            [['name'], 'string'],
            [['first_price', 'second_price', 'third_price'], 'number', 'min' => 0],
            [['price_type', 'is_auto_upgrade'], 'default', 'value' => 1],
            [['is_use'], 'default', 'value' => 0],
            [['detail'], 'string', 'max' => 80],
        ];
    }

    public function attributeLabels()
    {
        return [
            'level' => '分销商等级',
            'name' => '分销商等级名称',
            'first_price' => '一级分销佣金数（元）',
            'second_price' => '二级分销佣金数（元）',
            'third_price' => '三级分销佣金数（元）',
            'detail' => '等级说明',
            'condition_type' => '条件升级类型',
            'upgrade_type_goods' => '开启商品升级',
            'upgrade_type_condition' => '开启条件升级',
            'goods_warehouse_ids' => '商品仓库ID',
            'goods_type' => '购物方式',
            'goods_list' => '商品列表',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

            if ($this->price_type == 1) {
                if ($this->first_price > 100 || $this->second_price > 100 || $this->third_price > 100) {
                    throw new \Exception('分销佣金百分比不能大于100%');
                }
            }
            $level = DistributionLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $this->level]);
            if ($level) {
                if ($level->id != $this->id) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '选中的权重已经存在对应的记录'
                    ];
                }


            }
            $level = DistributionLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
            if ($level) {
                if ($level->level != $this->level) {
                    $distribution = Distribution::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $level->level])->exists();
                    if ($distribution) {
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '当前等级下已存在分销商，不可变更等级权重！'
                        ];
                    }
                }
            }
            if (!$level) {
                $level = new DistributionLevel();
                $level->is_delete = 0;
                $level->mall_id = \Yii::$app->mall->id;
            }
            $level->attributes = $this->attributes;
            $level->checked_condition_keys = SerializeHelper::encode($this->checked_condition_keys);
            $level->checked_condition_values = SerializeHelper::encode($this->checked_condition_values);
            $level->goods_list = SerializeHelper::encode($this->goods_list);
            $level->goods_warehouse_ids = SerializeHelper::encode($this->goods_warehouse_ids);


            if (!$level->save()) {

                return [
                    'code' => ApiCode::CODE_FAIL,
                    'msg' => '保存失败',
                    'error'=>$level->errors
                ];
            } else {
                return [
                    'code' => ApiCode::CODE_SUCCESS,
                    'msg' => '保存成功'
                ];
            }

    }


    public function getDetail()
    {

        $weights=DistributionLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();
        $level = DistributionLevel::findOne(['id' => $this->id, 'is_delete' => 0]);
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
                    'weights' =>$weights
                ]
            ];
        }

    }

}