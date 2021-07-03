<?php

namespace app\plugins\stock\models;

use app\models\BaseActiveRecord;
use app\models\User;
use Yii;


/**
 * This is the model class for table "{{%plugin_stock_goods_price_log}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $user_id
 * @property float $price
 * @property int $log_id
 * @property int $created_at
 * @property int $deleted_at
 * @property int $updated_at
 * @property int $is_delete
 * @property int $type 0商城卖货 1下级拿货
 * @property int $goods_id
 * @property int $buy_user_id
 * @property string $order_no
 */
class GoodsPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_stock_goods_price_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'log_id', 'goods_id', 'buy_user_id', 'order_no'], 'required'],
            [['mall_id', 'user_id', 'log_id', 'created_at', 'deleted_at', 'updated_at', 'is_delete', 'type', 'goods_id', 'buy_user_id'], 'integer'],
            [['price'], 'number'],
            [['order_no'], 'string', 'max' => 45],
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
            'price' => 'Price',
            'log_id' => 'Log ID',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
            'is_delete' => 'Is Delete',
            'type' => '0商城卖货 1下级拿货',
            'goods_id' => 'Goods ID',
            'buy_user_id' => 'Buy User ID',
            'order_no' => 'Order No',
        ];
    }
}
