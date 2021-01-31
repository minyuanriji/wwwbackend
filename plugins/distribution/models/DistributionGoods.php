<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_goods}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $goods_id
 * @property string|null $goods_type
 * @property int $attr_setting_type 按规格设置
 * @property int $share_type 佣金类型 0，固定金额，1百分比
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $is_alone
 *
 * @property DistributionGoodsDetail $distributionGoodsDetail
 */
class DistributionGoods extends BaseActiveRecord
{

    const TYPE_MALL_GOODS = 'MALL_GOODS';

    /** @var int 0按商品设置1按规格设置 */
    const ATTR_SETTING_TYPE_GOODS = 0;
    const ATTR_SETTING_TYPE_ATTR = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'goods_id'], 'required'],
            [['mall_id', 'goods_id', 'attr_setting_type', 'share_type', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'is_alone'], 'integer'],
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
            'mall_id' => 'Mall ID',
            'goods_id' => 'Goods ID',
            'goods_type' => 'Goods Type',
            'attr_setting_type' => '按规格设置',
            'share_type' => '佣金类型 0，固定金额，1百分比',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'is_alone' => '单独设置分销'
        ];
    }

    public function getDistributionGoodsDetail()
    {
        return $this->hasOne(DistributionGoodsDetail::class, ['distribution_goods_id' => 'id']);
    }
}
