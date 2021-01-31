<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_team}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $parent_level
 * @property int $child_level
 * @property int $price_type
 * @property float $price
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $created_at
 * @property int $is_delete
 * @property int $is_enable
 * @property string $name 配置名称
 */
class Team extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_team}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name'], 'required'],
            [['mall_id', 'parent_level', 'child_level', 'price_type', 'deleted_at', 'updated_at', 'created_at', 'is_delete', 'is_enable'], 'integer'],
            [['price'], 'number'],
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
            'parent_level' => 'Parent Level',
            'child_level' => 'Child Level',
            'price_type' => 'Price Type',
            'price' => 'Price',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'is_delete' => 'Is Delete',
            'is_enable' => 'Is Enable',
            'name' => '配置名称',
        ];
    }
}
