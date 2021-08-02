<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_rebuy_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property string $month
 * @property float $price
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property float $total_order_price
 * @property int $total_order_goods_num
 * @property User $user
 */
class RebuyPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_rebuy_price_log}}';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'month'], 'required'],
            [['mall_id', 'user_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete','total_order_goods_num'], 'integer'],
            [['price','total_order_price'], 'number'],
            [['month_num'], 'string', 'max' => 45],
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
            'month' => 'Month Num',
            'price' => 'Price',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'total_order_price'=>'总订单金额',
            'total_order_goods_num'=>'订单总商品数量'
        ];
    }


    public function getUser(){

        return $this->hasOne(User::class,['id'=>'user_id']);

    }
}
