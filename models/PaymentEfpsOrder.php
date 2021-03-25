<?php


namespace app\models;


class PaymentEfpsOrder extends BaseActiveRecord {

    public static function tableName(){
        return '{{%payment_efps_order}}';
    }

    public function rules(){
        return array_merge(parent::rules(), [
            [['outTradeNo', 'payment_order_union_id', 'customerCode', 'payAmount',
              'payCurrency', 'orderInfo', 'payAPI', 'payMethod', 'notifyUrl',
              'transactionStartTime', 'nonceStr', 'update_at', 'is_pay'], 'required']
        ]);
    }
}