<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_boss_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property int $price_type
 * @property int $equal_price_type
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $goods_type
 * @property int $is_alone 是否单独设置
 */
class BossGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'price_type', 'equal_price_type'], 'required'],
            [['mall_id', 'goods_id', 'created_at', 'updated_at', 'deleted_at', 'is_delete', 'goods_type', 'is_alone'], 'integer'],
            [['price_type', 'equal_price_type'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'price_type' => 'Boss Price Type',
            'equal_price_type' => 'Equal Price Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'goods_type' => 'Goods Type',
            'is_alone' => '是否单独设置',
        ];
    }



}
