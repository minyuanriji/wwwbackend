<?php

namespace app\plugins\agent\models;

use app\helpers\SerializeHelper;
use app\models\BaseActiveRecord;
use app\plugins\agent\forms\common\selfCommon;
use Exception;
use Yii;

/**
 * This is the model class for table "{{%plugin_agent_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property string $name
 * @property int $is_auto_upgrade
 * @property int $equal_price_type 平级奖佣金类型 1百分比 2 固定金额
 * @property string|null $detail
 * @property float $equal_price 平级奖
 * @property float $agent_price 团队奖
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string|null $checked_condition_values 升级条件
 * @property int $is_use 是否启用
 * @property string|null $checked_condition_keys 升级条件选中的key集合
 * @property int $condition_type 0 未选择、1满足其一  2、满足所有
 * @property int $upgrade_type_goods
 * @property int $upgrade_type_condition
 * @property string|null $goods_warehouse_ids 商品仓库的ID
 * @property string|null $goods_list 商品列表
 * @property int $goods_type 商品升级类型
 * @property int $agent_price_type 团队奖佣金类型 1 百分比 2 固定金额
 * @property int $buy_goods_type 购买商品计算方式 0 订单完成 1 订单支付
 * @property float $over_agent_price 被越级的奖励
 */
class AgentLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_agent_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'equal_price', 'agent_price'], 'required'],
            [[
                'mall_id', 'level', 'is_auto_upgrade', 'equal_price_type', 'created_at', 'updated_at', 
                'is_delete', 'is_use', 'condition_type', 'upgrade_type_goods', 'upgrade_type_condition', 
                'goods_type', 'agent_price_type','buy_goods_type'
            ], 'integer'],
            [[
                'detail', 'checked_condition_values', 'checked_condition_keys', 'goods_warehouse_ids',
                'goods_list','levelup_give_setting','invited_give_setting','levelup_integral_setting',
            ], 'string'],
            [['equal_price', 'agent_price','over_agent_price'], 'number'],
            [['name'], 'string', 'max' => 45],
            [['equal_price'], 'validateEqualPrice'],
            [['agent_price'], 'validateAgentPrice'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mall_id' => 'Mall ID',
            'level' => 'Level',
            'name' => 'Name',
            'is_auto_upgrade' => 'Is Auto Upgrade',
            'equal_price_type' => '平级奖佣金类型 1百分比 2 固定金额',
            'detail' => 'Detail',
            'equal_price' => '平级奖',
            'agent_price' => '团队奖',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'checked_condition_values' => '升级条件',
            'is_use' => '是否启用',
            'checked_condition_keys' => '升级条件选中的key集合',
            'condition_type' => '0 未选择、1满足其一  2、满足所有',
            'upgrade_type_goods' => 'Upgrade Type Goods',
            'upgrade_type_condition' => 'Upgrade Type Condition',
            'goods_warehouse_ids' => '商品仓库的ID',
            'goods_list' => '商品列表',
            'goods_type' => '商品升级类型',
            'agent_price_type' => '团队奖佣金类型 1 百分比 2 固定金额',
            'buy_goods_type'=>'购买商品计算方式',
            'over_agent_price'=>'被越级的奖励',
            'levelup_give_setting'=>'升级名额赠送设置',
            'invited_give_setting'=>'推广名额赠送设置',
            'levelup_integral_setting'=>'升级赠送金豆券设置',
        ];

    }

    public function validateEqualPrice($attribute, $params){
        //平级奖验证
        if ($this->equal_price_type == 0) {
            if ($this->equal_price > 100) {
                $this->addError($attribute, '平级奖佣金百分比不能大于100%');
            }
        }
    }

    public function validateAgentPrice($attribute, $params){
        //团队将奖验证
        if ($this->agent_price_type == 0) {
            if ($this->agent_price > 100) {
                $this->addError($attribute, '团队奖佣金百分比不能大于100%');
            }
        }
    }

    /**
     * 编辑/新增等级
     * @Author bing
     * @DateTime 2020-10-27 16:49:52
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $data
     * @return void
     */
    public static function setData($data){
        try {
            $id = $data['id'] ?? 0;
            $where = array(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id, 'level' => $data['level']]);
            $id > 0 && $where[] = array('!=', 'id', $id);
            $params = compact('where');
            $exist = self::count($params,1);
            if($exist)  throw new Exception('选中的权重已经存在对应的记录');
            
            $model = self::findOne(array('is_delete' => 0, 'mall_id' => Yii::$app->mall->id, 'id' => $id));
            if(empty($model)){
                //新建一个
                $model = new self();
                $model->is_delete = 0;
                $model->mall_id = Yii::$app->mall->id;
            }else{
                if($model->level != $data['level']){
                    //等级权重有变化
                    $has_agent = self::count(array('where'=>array(['is_delete' => 0, 'mall_id' => Yii::$app->mall->id, 'level' => $model->level])),1);
                    if($has_agent) throw new Exception('当前等级下已存在分销商，不可变更等级权重！');
                }
            }
            $model->attributes = $data;
            $model->checked_condition_keys = json_encode($data['checked_condition_keys'] ?? []);
            $model->checked_condition_values = json_encode($data['checked_condition_values'] ?? []);
            $model->goods_list = json_encode($data['goods_list'] ?? []);
            $model->goods_warehouse_ids = json_encode($data['goods_warehouse_ids'] ?? []);
            $model->levelup_integral_setting = json_encode($data['levelup_integral_setting'] ?? []);
            $res = $model->save();
            if($res === false) throw new Exception($model->getErrorMessage());
            return true;
        } catch (Exception $e) {
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取等级详情
     * @Author bing
     * @DateTime 2020-10-28 09:49:51
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @return void
     */
    public static function getDetail($id){
        $level = self::findOne(array('id' => $id, 'is_delete' => 0));
        if(!empty($level)){
            $level->checked_condition_values = json_decode($level->checked_condition_values,true);
            $level->checked_condition_keys = json_decode($level->checked_condition_keys,true);
            $level->goods_warehouse_ids = json_decode($level->goods_warehouse_ids,true);
            $level->goods_list = json_decode($level->goods_list,true);
            $level->levelup_integral_setting = json_decode($level->levelup_integral_setting ,true);
        }
        return $level;
    }


    /**
     * 设置名额
     * @Author bing
     * @DateTime 2020-10-28 13:10:08
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param [type] $data
     * @return void
     */
    public static function levelNumSetting($data){
        try{
            if(!isset($data['levelup_give_setting'])) throw new Exception('levelup_give_setting参数错误');
            if(!isset($data['invited_give_setting'])) throw new Exception('invited_give_setting参数错误');
            $id = $data['id'] ?? 0;
            $level = self::findOne(array('id' => $id, 'is_delete' => 0));
            if(empty($level)) throw new Exception('未找到相应经销商等级');
            // 获取全部可用等级数组
            $all_levels = self::find()->select('level')->where(array('is_delete' => 0))->asArray()->column();
            //设置的等级不存在
            $diff = array_diff(array_keys($data['levelup_give_setting']),$all_levels);
            if(!empty($diff)) throw new Exception('升级赠送的等级不存在，或已删除');
            $level->levelup_give_setting = json_encode($data['levelup_give_setting']);
            //设置的等级不存在
            $diff = array_diff(array_keys($data['invited_give_setting']),$all_levels);
            if(!empty($diff)) throw new Exception('推广的等级不存在，或已删除');
            $level->invited_give_setting = json_encode($data['invited_give_setting']);
            $res = $level->save();
            if($res === false) throw new Exception($level->getErrorMessage());
            return true;
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取名额信息
     * @Author bing
     * @DateTime 2020-10-28 14:17:01
     * @copyright: Copyright (c) 2020 广东七件事集团
     * @param int $id
     * @return void
     */
    public static function getLevelNumSetting(int $id){
        try{
            $level = self::find()->where(array('id' => $id, 'is_delete' => 0))->asArray()->one();
            if(empty($level)) throw new Exception('未找到相应经销商等级');
            $levelup_give_setting = json_decode($level['levelup_give_setting'],true);
            $invited_give_setting = json_decode($level['invited_give_setting'],true);
            return compact('levelup_give_setting','invited_give_setting');
        }catch(Exception $e){
            self::$error = $e->getMessage();
            return false;
        }
    }
}
