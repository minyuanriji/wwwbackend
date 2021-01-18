<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $common_order_detail_id
 * @property float $price
 * @property int $is_price 是否发放
 * @property int $status 是否有效，-1 无效  1有效
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $level
 * @property int $child_id
 */
class DistributionLog extends B
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'common_order_detail_id', 'created_at', 'deleted_at', 'updated_at', 'level', 'child_id'], 'required'],
            [['mall_id', 'user_id', 'common_order_detail_id', 'is_price', 'status', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'level', 'child_id'], 'integer'],
            [['price'], 'number'],
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
            'user_id' => 'User ID',
            'common_order_detail_id' => 'Common Order Detail ID',
            'price' => 'Price',
            'is_price' => '是否发放',
            'status' => '是否有效，-1 无效  1有效',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'level' => 'Level',
            'child_id' => 'Child ID',
        ];
    }
}
