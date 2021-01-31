<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\area\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\area\forms\common\AreaLevelCommon;
use app\plugins\area\models\Area;
use app\plugins\area\models\AreaLevel;

class AreaLevelEditForm extends BaseModel
{
    public $id;
    public $level;
    public $name;
    public $checked_condition_keys;
    public $checked_condition_values;
    public $equal_price;
    public $area_price;
    public $equal_price_type;
    public $area_price_type;
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
    public $over_area_price;


    public function rules()
    {
        return [
            [['level', 'name', 'area_price_type', 'is_use'], 'required'],
            [['level', 'name', 'checked_condition_keys', 'checked_condition_values', 'equal_price_type', 'area_price_type', 'is_use', 'detail', 'goods_warehouse_ids', 'goods_list'], 'trim'],
            [['level', 'area_price_type', 'equal_price_type', 'is_use', 'id', 'is_auto_upgrade', 'condition_type', 'upgrade_type_condition', 'upgrade_type_goods', 'goods_type', 'buy_goods_type'], 'integer'],
            [['name'], 'string'],
            [['equal_price', 'area_price','over_area_price'], 'number', 'min' => 0],
            [['area_price_type', 'equal_price_type', 'is_auto_upgrade', 'buy_goods_type'], 'default', 'value' => 1],
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
            'upgrade_type_goods' => '开启商品升级',
            'upgrade_type_condition' => '开启条件升级',
            'goods_warehouse_ids' => '商品仓库ID',
            'goods_type' => '购物方式',
            'goods_list' => '商品列表',
            'equal_price' => '平级奖',
            'equal_price_type' => '平级奖佣金类型',
            'area_price' => '经销商奖励',
            'area_price_type' => '经销商佣金类型',
            'buy_goods_type' => '结算状态',
            'over_area_price'=>'越级奖励'
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            if ($this->equal_price_type == 1) {
                if ($this->equal_price > 100) {
                    throw new \Exception('平级奖佣金百分比不能大于100%');
                }
            }
            if ($this->area_price_type == 1) {
                if ($this->area_price > 100) {
                    throw new \Exception('团队奖佣金百分比不能大于100%');
                }
            }

            $level = AreaLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $this->level]);
            if ($level) {
                if ($level->id != $this->id) {
                    return [
                        'code' => ApiCode::CODE_FAIL,
                        'msg' => '选中的权重已经存在对应的记录'
                    ];
                }
            }
            $level = AreaLevel::findOne(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'id' => $this->id]);
            if ($level) {
                if ($level->level != $this->level) {
                    $area = Area::find()->where(['is_delete' => 0, 'mall_id' => \Yii::$app->mall->id, 'level' => $level->level])->exists();
                    if ($area) {
                        return [
                            'code' => ApiCode::CODE_FAIL,
                            'msg' => '当前等级下已存在分销商，不可变更等级权重！'
                        ];
                    }
                }
            }
            if (!$level) {
                $level = new AreaLevel();
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

        $weights = AreaLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();


        $level = AreaLevel::findOne(['id' => $this->id, 'is_delete' => 0]);


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