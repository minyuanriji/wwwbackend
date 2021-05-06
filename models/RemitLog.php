<?php

namespace app\models;


use Yii;

/**
 * This is the model class for table "{{%remit_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property float $price
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property string|null $content 汇款原因
 * @property string|null $type
 * @property int $operator_id
 */
class RemitLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%remit_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'operator_id'], 'required'],
            [['mall_id', 'user_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'operator_id'], 'integer'],
            [['price'], 'number'],
            [['content'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 45],
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
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'content' => '汇款原因',
            'type' => 'Type',
            'operator_id' => 'Operator ID',
        ];
    }
}