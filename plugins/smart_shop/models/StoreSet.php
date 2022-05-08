<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class StoreSet extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_store_set}}';
    }

    public function rules(){
        return [
            [['mall_id', 'bsh_mch_id', 'ss_mch_id', 'ss_store_id', 'transfer_rate', 'created_at', 'updated_at'], 'required']
        ];
    }

}