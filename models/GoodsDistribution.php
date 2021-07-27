<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%goods_distribution}}".
 *
 * @property int $id
 * @property string $distribution_commission_first 一级分销佣金比例
 * @property string $distribution_commission_second 二级分销佣金比例
 * @property string $distribution_commission_third 三级分销佣金比例
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $is_delete
 * @property int $level 分销商等级
 */
class GoodsDistribution extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%goods_distribution}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['distribution_commission_first', 'distribution_commission_second', 'distribution_commission_third'], 'number'],
            [['goods_id'], 'required'],
            [['goods_id', 'goods_attr_id', 'is_delete', 'level'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'distribution_commission_first' => '一级分销佣金比例',
            'distribution_commission_second' => '二级分销佣金比例',
            'distribution_commission_third' => '三级分销佣金比例',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
            'level' => '分销商等级',
        ];
    }
}
