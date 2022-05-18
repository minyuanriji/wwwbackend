<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class StoreAccountLog extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_store_account_log}}';
    }

    public function rules(){
        return [
            [['mall_id', 'ss_mch_id', 'ss_store_id', 'created_at', 'source_type', 'source_id', 'type', 'before_num', 'num', 'desc'], 'required']
        ];
    }

}