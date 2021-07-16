<?php
namespace app\plugins\addcredit\models;

use app\models\BaseActiveRecord;

class AddcreditOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_addcredit_order}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'plateform_id', 'user_id', 'mobile', 'order_no',
                'order_price', 'order_status', 'created_at', 'updated_at',
                'pay_status'], 'required'],
            [['pay_at', 'pay_price', 'pay_type', 'integral_deduction_price',
              'integral_fee_rate', 'plateform_request_data', 'plateform_response_data'], 'safe']
        ];
    }
}