<?php
namespace app\models;


class EfpsTransferOrder extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%efps_transfer_order}}';
    }

    public function rules(){
        return [
            [['status'], 'required'],
            [['outTradeNo', 'customerCode', 'notifyUrl', 'amount',
              'bankUserName', 'bankCardNo', 'bankName', 'bankAccountType',
              'remark', 'fail_retry_count', 'created_at', 'updated_at'], 'safe']
        ];
    }
}