<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-05-08
 * Time: 17:53
 */

namespace app\plugins\agent\forms\mall;

use app\core\ApiCode;
use app\helpers\SerializeHelper;
use app\models\BaseModel;
use app\plugins\agent\forms\common\AgentLevelCommon;
use app\plugins\agent\models\Agent;
use app\plugins\agent\models\AgentLevel;

class AgentLevelEditForm extends BaseModel
{
    public $id;
    public $level;
    public $name;
    public $checked_condition_keys;
    public $checked_condition_values;
    public $equal_price;
    public $agent_price;
    public $equal_price_type;
    public $agent_price_type;
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
    public $over_agent_price;


    public function rules()
    {
        return [
            [['level', 'name', 'agent_price_type', 'is_use'], 'required'],
            [['level', 'name', 'checked_condition_keys', 'checked_condition_values', 'equal_price_type', 'agent_price_type', 'is_use', 'detail', 'goods_warehouse_ids', 'goods_list'], 'trim'],
            [['level', 'agent_price_type', 'equal_price_type', 'is_use', 'id', 'is_auto_upgrade', 'condition_type', 'upgrade_type_condition', 'upgrade_type_goods', 'goods_type', 'buy_goods_type'], 'integer'],
            [['name'], 'string'],
            [['equal_price', 'agent_price','over_agent_price'], 'number', 'min' => 0],
            [['agent_price_type', 'equal_price_type', 'is_auto_upgrade', 'buy_goods_type'], 'default', 'value' => 1],
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
            'agent_price' => '经销商奖励',
            'agent_price_type' => '经销商佣金类型',
            'buy_goods_type' => '结算状态',
            'over_agent_price'=>'越级奖励'
        ];
    }
    

    public function getDetail()
    {

        $weights = AgentLevelCommon::getInstance(\Yii::$app->mall)->getLevelWeights();


        $level = AgentLevel::findOne(['id' => $this->id, 'is_delete' => 0]);


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