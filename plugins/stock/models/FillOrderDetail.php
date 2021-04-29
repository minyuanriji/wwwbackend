<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\Goods;
use Yii;

/**
 * This is the model class for table "{{%plugin_stock_fill_order_detail}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $goods_id
 * @property int $num
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $is_give
 * @property float $sale_price
 * @property float $price
 * @property float $fill_price
 */
class FillOrderDetail extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_fill_order_detail}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'goods_id'], 'required'],
            [['mall_id', 'order_id', 'goods_id', 'num', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'is_give'], 'integer'],
            [['sale_price','price','fill_price'],'number']
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
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'num' => 'Num',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'is_give' => 'Is Give',
            'sale_price'=>'商品售价',
            'price'=>'支付价格',
            'fill_price'=>'补货奖励'
        ];
    }
}
