<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_subsidy_price_job}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $queue_id 队列的ID
 * @property string $month 结算的月
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class SubsidyPriceJob extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_subsidy_price_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'queue_id', 'month'], 'required'],
            [['mall_id', 'queue_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['month'], 'string', 'max' => 45],
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
            'queue_id' => '队列的ID',
            'month' => '结算的月',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
