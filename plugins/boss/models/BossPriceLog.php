<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;



/**
 * This is the model class for table "{{%plugin_boss_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property float $price
 * @property int $is_delete
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $type 0 永久分红 1 额外分红
 * @property int $start_time
 * @property int $end_time
 */
class BossPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id'], 'required'],
            [['mall_id', 'user_id', 'is_delete', 'created_at', 'deleted_at', 'updated_at', 'type', 'start_time', 'end_time'], 'integer'],
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
            'price' => 'Price',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'type' => '0 永久分红 1 额外分红',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }

}
