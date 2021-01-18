<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_subsidy_setting}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $distribution_level
 * @property float $price
 * @property int $min_num
 * @property int $max_num
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string $name
 * @property int $is_enable 是否启用
 */
class SubsidySetting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_subsidy_setting}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'distribution_level'], 'required'],
            [['name'],'string'],
            [['mall_id', 'distribution_level', 'min_num', 'max_num', 'deleted_at', 'created_at', 'updated_at', 'is_delete', 'is_enable'], 'integer'],
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
            'distribution_level' => 'Distribution Level',
            'price' => 'Price',
            'min_num' => 'Min Num',
            'max_num' => 'Max Num',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'is_enable' => '是否启用',
            'name'=>'名称'
        ];
    }
}
