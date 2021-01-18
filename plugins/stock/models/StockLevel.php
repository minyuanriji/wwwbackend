<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%plugin_stock_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property string $name
 * @property int $is_auto_upgrade
 * @property string|null $detail
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string|null $checked_condition_values 升级条件
 * @property int $is_use 是否启用
 * @property string|null $checked_condition_keys 升级条件选中的key集合
 * @property int $condition_type 0 未选择、1满足其一  2、满足所有
 * @property int $upgrade_type_condition
 * @property float $service_price 代理商服务费
 * @property int $service_price_type 服务费类型 1百分比 2 固定金额
 * @property int $sub_stock_rate 减库存比例
 * @property int $is_fill
 * @property int $is_over
 * @property int $is_equal
 *
 *
 */
class StockLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id','is_equal','is_fill','is_over','sub_stock_rate', 'level', 'is_auto_upgrade', 'created_at', 'updated_at', 'is_delete', 'is_use', 'condition_type', 'upgrade_type_condition', 'service_price_type'], 'integer'],
            [['detail', 'checked_condition_values', 'checked_condition_keys'], 'string'],
            [['service_price'], 'number'],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'checked_condition_values' => '升级条件',
            'is_use' => '是否启用',
            'checked_condition_keys' => '升级条件选中的key集合',
            'condition_type' => '0 未选择、1满足其一  2、满足所有',
            'upgrade_type_condition' => 'Upgrade Type Condition',
            'stock_price_type' => '团队奖佣金类型 1 百分比 2 固定金额',
            'service_price_type' => '服务费类型',
            'sub_stock_rate'=>'减库存比例',
            'is_fill'=>'补货奖励',
            'is_over'=>'超越奖励',
            'is_equal'=>'平级奖励',
        ];

    }
}
