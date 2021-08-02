<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_goods_detail}}".
 *
 * @property int $id
 * @property float $commission_first 一级分销佣金比例
 * @property float $commission_second 二级分销佣金比例
 * @property float $commission_third 三级分销佣金比例
 * @property int $goods_id
 * @property int $goods_attr_id
 * @property int $is_delete
 * @property int $level 分销商等级
 * @property string $goods_type 商品类型 MALL_GOODS、
 * @property int $distribution_goods_id
 */
class DistributionGoodsDetail extends BaseActiveRecord
{

    const TYPE_MALL_GOODS='MALL_GOODS';//商城商品

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_goods_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['commission_first', 'commission_second', 'commission_third'], 'number'],
            [['goods_id'], 'required'],
            [['goods_id', 'goods_attr_id', 'is_delete', 'level','distribution_goods_id'], 'integer'],
            [['goods_type'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'commission_first' => '一级分销佣金比例',
            'commission_second' => '二级分销佣金比例',
            'commission_third' => '三级分销佣金比例',
            'goods_id' => 'Goods ID',
            'goods_attr_id' => 'Goods Attr ID',
            'is_delete' => 'Is Delete',
            'level' => '分销商等级',
            'goods_type' => '商品类型 MALL_GOODS、',
            'distribution_goods_id'=>'distribution_goods_id '
        ];
    }
}