<?php

namespace app\plugins\boss\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_boss_goods_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $level
 * @property float $boss_price
 * @property float $equal_price
 * @property float $over_boss_price
 * @property int $is_delete
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_at
 * @property int $goods_id
 * @property int $boss_goods_id
 */
class BossGoodsDetail extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_boss_goods_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'level', 'boss_price', 'equal_price', 'over_boss_price','goods_id'], 'required'],
            [['mall_id', 'level', 'is_delete', 'updated_at', 'deleted_at', 'created_at','goods_id','boss_goods_id'], 'integer'],
            [['boss_price', 'equal_price', 'over_boss_price'], 'number'],
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
            'level' => 'Level',
            'boss_price' => 'Boss Price',
            'equal_price' => 'Equal Price',
            'over_boss_price' => 'Over Boss Price',
            'is_delete' => 'Is Delete',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',

            'goods_id'=>'商品ID',
            'boss_goods_id'=>'boss_goods_id'
        ];
    }
}