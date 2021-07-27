<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_level}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property string $name
 * @property int $is_auto_upgrade
 * @property int $price_type 佣金类型 1百分比 2 固定金额
 * @property string|null $detail
 * @property float $first_price
 * @property float $second_price
 * @property float $third_price
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $condition_type
 * @property int $upgrade_type_goods
 * @property int $upgrade_type_condition
 * @property string|null $checked_condition_values 升级条件
 * @property string|null $goods_list 商品列表
 * @property string|null $goods_warehouse_ids 商品仓库ID
 * @property int $is_use 是否启用
 * @property int $goods_type
 * @property string|null $checked_condition_keys 选中条件的key数组
 */
class DistributionLevel extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_level}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name', 'first_price', 'second_price', 'third_price'], 'required'],
            [['mall_id', 'level', 'is_auto_upgrade', 'price_type', 'created_at', 'updated_at', 'is_delete', 'is_use', 'condition_type','upgrade_type_condition','upgrade_type_goods','goods_type'], 'integer'],
            [['detail', 'checked_condition_values', 'checked_condition_keys','goods_warehouse_ids','goods_list'], 'string'],
            [['first_price', 'second_price', 'third_price'], 'number'],
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
            'price_type' => '佣金类型 1百分比 2 固定金额',
            'detail' => 'Detail',
            'first_price' => 'First Price',
            'second_price' => 'Second Price',
            'third_price' => 'Third Price',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'checked_condition_values' => '升级条件',
            'is_use' => '是否启用',
            'checked_condition_keys' => '选中条件的key数组',
            'condition_type' => '条件升级的方式',
            'upgrade_type_condition' => '开启条件升级',
            'upgrade_type_goods' => '开启商品升级',
            'goods_warehouse_ids'=>'商品仓库ID',
            'goods_type'=>'购物方式',
            'goods_list'=>'商品列表',
        ];
    }
}
