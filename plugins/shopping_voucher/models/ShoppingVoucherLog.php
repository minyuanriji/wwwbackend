<?php
namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherLog extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_log}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'user_id', 'type', 'current_money', 'money',
              'desc', 'source_id', 'source_type', 'created_at', 'updated_at'], 'required']
        ];
    }
}
