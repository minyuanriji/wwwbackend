<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class Cyorder extends BaseActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_cyorder}}';
    }

    public function rules(){
        return [
            [['mall_id', 'cyorder_id', 'created_at', 'updated_at', 'bsh_mch_id', 'ss_mch_id', 'ss_store_id'], 'required'],
            [['pay_price', 'pay_user_mobile',  'commission_3r_status',
              'commission_status', 'commission_info', 'commission_text',
              'commission_3r_status', 'commission_3r_info', 'commission_3r_text',
              'shopping_voucher_status', 'shopping_voucher_info', 'shopping_voucher_error',
              'score_status', 'score_info', 'score_error',
              'transfer_rate', 'status', 'error_text', 'cyorder_data'], 'safe']
        ];
    }
}