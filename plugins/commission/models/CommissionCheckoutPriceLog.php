<?php
namespace app\plugins\commission\models;


use app\models\BaseActiveRecord;

class CommissionCheckoutPriceLog extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_commission_checkout_price_log}}';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'checkout_order_id', 'user_id', 'price', 'status', 'created_at', 'updated_at'], 'required'],
            [['rule_data_json'], 'safe']
        ];
    }
}