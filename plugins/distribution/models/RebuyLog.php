<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;

use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_rebuy_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property float $price
 * @property int $id_delete
 * @property int $deleted_at
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_price
 * @property int $goods_type 商品类型
 * @property int $status
 * @property int $common_order_detail_id
 * @property int $num
 */
class RebuyLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_rebuy_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'price'], 'required'],
            [['mall_id', 'user_id', 'goods_id', 'id_delete', 'deleted_at', 'created_at', 'updated_at', 'is_price', 'goods_type','status','common_order_detail_id','num'], 'integer'],
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
            'goods_id' => 'Goods ID',
            'price' => 'Price',
            'id_delete' => 'Id Delete',
            'deleted_at' => 'Deleted At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'is_price' => 'Is Price',
            'goods_type' => '商品类型',
            'status'=>'状态',
            'common_order_detail_id'=>'公共订单详情ID',
            'num'=>'数量'
        ];
    }
}
