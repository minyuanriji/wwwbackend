<?php

namespace app\models;

class PaymentPrepare extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%payment_prepare}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'created_at', 'source_table', 'prepare_class'], 'required'],
            [['token'], 'trim'],
            [['order_id'], 'safe']
        ];
    }
}