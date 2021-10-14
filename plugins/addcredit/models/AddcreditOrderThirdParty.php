<?php

namespace app\plugins\addcredit\models;

use app\models\BaseActiveRecord;

class AddcreditOrderThirdParty extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_addcredit_order_third_party}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'order_id', 'process_status', 'unique_order_no', 'created_at'], 'required'],
            [['plateform_request_data', 'plateform_response_data', 'updated_at', 'remark', 'next_query_time'], 'safe']
        ];
    }

}
