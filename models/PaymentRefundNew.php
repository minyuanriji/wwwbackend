<?php
namespace app\models;

class PaymentRefundNew extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_refund_new}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'payment_order_id', 'order_no', 'pay_type', 'created_at', 'updated_at'], 'required'],
            [['is_pay'], 'integer'],
            [['amount'], 'number']
        ];
    }
}