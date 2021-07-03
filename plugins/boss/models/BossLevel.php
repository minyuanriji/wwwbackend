<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;

/**
 * This is the model class for table "{{%plugin_boss_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property string $name
 * @property int $is_auto_upgrade
 * @property string|null $detail
 * @property float $price 平级奖
 * @property float $extra_price 团队奖
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string|null $checked_condition_values 升级条件
 * @property int $is_enable 是否启用
 * @property string|null $checked_condition_keys 升级条件选中的key集合
 * @property int $condition_type 0 未选择、1满足其一  2、满足所有
 * @property int $upgrade_type_goods
 * @property int $upgrade_type_condition
 * @property string|null $goods_warehouse_ids 商品仓库的ID
 * @property string|null $goods_list 商品列表
 * @property int $goods_type 商品升级类型
 * @property int $buy_goods_type 0订单完成 1下单 完成
 * @property float $extra_limit_price 额外奖励上限金额
 * @property int $extra_type 存在额外奖励的条件 1 购物 2 条件 3 任意
 * @property int $is_extra 是否存在额外奖励
 * @property int $extra_is_limit 额外奖励是否存在上限
 */
class BossLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id', 'level', 'is_auto_upgrade', 'created_at', 'updated_at', 'is_delete', 'is_enable', 'condition_type', 'upgrade_type_goods', 'upgrade_type_condition', 'goods_type', 'buy_goods_type', 'extra_type', 'is_extra', 'extra_is_limit'], 'integer'],
            [['detail', 'checked_condition_values', 'checked_condition_keys', 'goods_warehouse_ids', 'goods_list'], 'string'],
            [['price', 'extra_price', 'extra_limit_price'], 'number'],
            [['name'], 'string', 'max' => 45],
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
            'detail' => 'Detail',
            'price' => '平级奖',
            'extra_price' => '团队奖',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'checked_condition_values' => '升级条件',
            'is_enable' => '是否启用',
            'checked_condition_keys' => '升级条件选中的key集合',
            'condition_type' => '0 未选择、1满足其一  2、满足所有',
            'upgrade_type_goods' => 'Upgrade Type Goods',
            'upgrade_type_condition' => 'Upgrade Type Condition',
            'goods_warehouse_ids' => '商品仓库的ID',
            'goods_list' => '商品列表',
            'goods_type' => '商品升级类型',
            'buy_goods_type' => '0订单完成 1下单 完成',
            'extra_limit_price' => '额外奖励上限金额',
            'extra_type' => '存在额外奖励的条件 1 购物 2 条件 3 任意',
            'is_extra' => '是否存在额外奖励',
            'extra_is_limit' => '额外奖励是否存在上限',
        ];
    }

    public function getBoss()
    {
        return $this->hasOne(Boss::class, ['level_id' => 'id']);
    }
}
