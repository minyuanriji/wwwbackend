<?php

namespace app\plugins\addcredit\models;

use app\models\BaseActiveRecord;

class AddcreditOrderRefund extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_addcredit_order_refund}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'created_at'], 'required'],
            [['reason', 'refund_price', 'refund_integral'], 'safe']
        ];
    }
}

