<?php
namespace app\plugins\oil\models;


use app\models\BaseActiveRecord;

class OilOrders extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_orders}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'user_id', 'product_id', 'order_no', 'order_status', 'order_price', 'created_at', 'updated_at', 'pay_status'], 'required'],
            [['pay_at', 'pay_price', 'pay_type', 'integral_deduction_price', 'integral_fee_rate'], 'safe']
        ];
    }
}