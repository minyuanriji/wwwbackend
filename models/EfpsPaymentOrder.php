<?php


namespace app\models;


class EfpsPaymentOrder extends BaseActiveRecord {

    public static function tableName(){
        return '{{%efps_payment_order}}';
    }

    public function rules(){
        return array_merge(parent::rules(), [
            [['outTradeNo', 'payment_order_union_id', 'customerCode', 'payAmount',
              'payCurrency', 'orderInfo', 'payAPI', 'payMethod', 'notifyUrl',
              'transactionStartTime', 'nonceStr', 'update_at', 'is_pay'], 'required'],
            [['redirectUrl', 'do_query_count'], 'safe']
        ]);
    }
}