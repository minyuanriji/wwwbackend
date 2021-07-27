<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%free_delivery_rules}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $mch_id
 * @property string $name
 * @property float $price
 * @property string $detail
 * @property int $is_delete
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 */
class FreeDeliveryRules extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%free_delivery_rules}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'detail'], 'required'],
            [['mall_id', 'detail'], 'required', "on" => "store"],
            [['mall_id', 'mch_id', 'is_delete', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['price'], 'number'],
            [['detail'], 'string'],
            [['name'], 'string', 'max' => 255],
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
            'mch_id' => 'Mch ID',
            'name' => 'Name',
            'price' => 'Price',
            'detail' => 'Detail',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function decodeDetail()
    {
        return Yii::$app->serializer->decode($this->detail);
    }
}
