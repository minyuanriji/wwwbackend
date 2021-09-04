<?php

namespace app\plugins\Shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherSendLog extends BaseActiveRecord{


    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_send_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'user_id', 'source_id', 'source_type', 'status', 'money', 'created_at', 'updated_at'], 'required'],
            [['data_json', 'remark'], 'safe']
        ];
    }
}