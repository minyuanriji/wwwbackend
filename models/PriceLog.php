<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property float $price
 * @property int $created_at
 * @property int $updated_at
 * @property int $order_id 订单ID
 */
class PriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'created_at', 'updated_at'], 'required'],
            [['mall_id', 'user_id', 'created_at', 'updated_at', 'order_id'], 'integer'],
            [['price'], 'number']
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
            'updated_at' => 'Updated At',
            'order_id' => '订单ID',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
