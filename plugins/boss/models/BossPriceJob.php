<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_boss_price_job}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property string $date
 * @property int $queue_id
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 */
class BossPriceJob extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_price_job}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'date', 'queue_id'], 'required'],
            [['mall_id', 'queue_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at'], 'integer'],
            [['date'], 'string', 'max' => 45],
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
            'date' => 'Date',
            'queue_id' => 'Queue ID',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }
}
