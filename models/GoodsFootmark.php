<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_footmark}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property Goods $goods
 */
class GoodsFootmark extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_footmark}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
        ];
    }

    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'goods_id']);
    }
}
