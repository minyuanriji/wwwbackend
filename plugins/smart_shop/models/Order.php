<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class Order extends BaseActiveRecord {

    const STATUS_UNCONFIRMED = 0;
    const STATUS_PROCESSING  = 1;
    const STATUS_FINISHED    = 2;
    const STATUS_CANCELED    = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_order}}';
    }

    public function rules(){
        return [
            [['mall_id', 'bsh_mch_id', 'created_at', 'updated_at', 'from_table_name', 'from_table_record_id', 'ss_mch_id', 'ss_store_id', 'status'], 'required'],
            [['split_data', 'is_delete', 'pay_price', 'pay_user_mobile', 'error_text', 'commission_status', 'commission_3r_status'], 'safe']
        ];
    }

}