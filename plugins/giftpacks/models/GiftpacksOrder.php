<?php

namespace app\plugins\giftpacks\models;


use app\models\BaseActiveRecord;

class GiftpacksOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_giftpacks_order}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'pack_id', 'user_id', 'order_sn', 'created_at', 'updated_at', 'pay_status'], 'required'],
            [['pay_at', 'pay_price', 'pay_type', 'integral_deduction_price', 'integral_fee_rate', 'is_delete',
              'commission_status', 'commission_remark', 'process_class'], 'safe']
        ];
    }

    public function getGiftpacks()
    {
        return $this->hasOne(Giftpacks::className(), ['id' => 'pack_id']);
    }


}
