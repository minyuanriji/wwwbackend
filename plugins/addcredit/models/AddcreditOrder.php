<?php

namespace app\plugins\addcredit\models;

use app\models\BaseActiveRecord;

class AddcreditOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */

    //已支付
    const PAY_TYPE_PAID = 'paid';

    //已退款
    const PAY_TYPE_REFUND = 'refund';

    //未支付
    const PAY_TYPE_UNP = 'unpaid';

    //退款中
    const PAY_TYPE_REF = 'refunding';

    //充值中
    const ORDER_STATUS_PRO = 'processing';

    //成功
    const ORDER_STATUS_SUC = 'success';

    //失败
    const ORDER_STATUS_FAIL = 'fail';

    //未付款
    const ORDER_STATUS_UNP = 'unpaid';

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