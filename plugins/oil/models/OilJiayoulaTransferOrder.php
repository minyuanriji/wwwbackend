<?php

namespace app\plugins\oil\models;

use app\models\BaseActiveRecord;

class OilJiayoulaTransferOrder extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_oil_jiayoula_transfer_order}}';
    }

    public function rules(){
        return [
            [['mall_id', 'order_sn', 'created_at', 'updated_at', 'amount', 'status', 'bankUserName', 'bankCardNo',
              'bankName', 'bankAccountType', 'originAmount', 'transferRate', 'oil_order_id'], 'required'],
            [[], 'safe']
        ];
    }
}