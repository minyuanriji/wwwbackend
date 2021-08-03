<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksGroupPayOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks_group_payorder}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'group_id', 'user_id', 'pay_status'], 'required'],
            [['remark', 'integral_fee_rate', 'pay_at', 'pay_price', 'pay_type', 'integral_deduction_price'], 'safe']
        ];
    }
}




