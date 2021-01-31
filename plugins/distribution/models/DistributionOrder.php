<?php

namespace app\plugins\distribution\models;

use app\models\BaseActiveRecord;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use Yii;

/**
 * This is the model class for table "{{%plugin_distribution_order}}".
 *
 * @property int $id
 * @property int $mall_id
 * @property int $order_id
 * @property int $order_detail_id
 * @property int $user_id 购物者用户id
 * @property int $first_parent_id 上一级用户id
 * @property int $second_parent_id 上二级用户id
 * @property int $third_parent_id 上三级用户id
 * @property string $first_price 上一级分销佣金
 * @property string $second_price 上二级分销佣金
 * @property string $third_price 上三级分销佣金
 * @property int $is_transfer 佣金发放状态：0=未发放，1=已发放
 * @property int $is_refund 是否退款：0=未退款，1=已退款
 * @property int $is_delete
 * @property int $is_pay 是否已支付0未支付1已支付
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property Order $order
 * @property User $user
 * @property OrderDetail $orderDetail
 */
class DistributionOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_distribution_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'user_id'], 'required'],
            [['mall_id', 'order_id', 'order_detail_id', 'user_id', 'first_parent_id', 'second_parent_id', 'third_parent_id', 'is_transfer', 'is_delete', 'is_refund','is_pay'], 'integer'],
            [['first_price', 'second_price', 'third_price'], 'number'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
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
            'order_detail_id' => 'Order Detail ID',
            'user_id' => '购物者用户id',
            'first_parent_id' => '上一级用户id',
            'second_parent_id' => '上二级用户id',
            'third_parent_id' => '上三级用户id',
            'first_price' => '上一级分销佣金',
            'second_price' => '上二级分销佣金',
            'third_price' => '上三级分销佣金',
            'is_refund' => '是否退款',
            'is_pay' => '是否支付0未1是',
            'is_transfer' => '佣金发放状态：0=未发放，1=已发放',
            'is_delete' => 'Is Delete',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getOrderDetail()
    {
        return $this->hasOne(OrderDetail::className(), ['id' => 'order_detail_id']);
    }
}
