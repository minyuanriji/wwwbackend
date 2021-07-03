<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;


/**
 * This is the model class for table "{{%plugin_stock_fill_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property int $goods_id
 * @property int $num
 * @property int $fill_order_detail_id
 * @property float $price
 * @property int $updated_at
 * @property int $created_at
 * @property int $deleted_at
 * @property int $is_delete
 * @property int $status
 * @property int is_price
 * @property float $income
 * @property int $order_id
 * @property User $user
 */
class FillPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_fill_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'goods_id', 'num', 'fill_order_detail_id'], 'required'],
            [['mall_id', 'order_id', 'user_id', 'is_price', 'goods_id', 'num', 'fill_order_detail_id', 'updated_at', 'created_at', 'deleted_at', 'is_delete', 'status'], 'integer'],
            [['price', 'income'], 'number'],
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
            'num' => 'Num',
            'fill_order_detail_id' => 'fill_order_detail_id',
            'price' => 'Price',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'is_delete' => 'Is Delete',
            'status' => '状态 1 有效  -1 无效',
            'income' => '收益',
            'is_price' => '是否发放佣金',
            'order_id' => '订单ID'
        ];
    }
    public function getUser(){


        return $this->hasOne(User::class,['id'=>'user_id']);

    }
}
