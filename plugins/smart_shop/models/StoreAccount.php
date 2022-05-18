<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class StoreAccount extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_store_account}}';
    }

    public function rules(){
        return [
            [['mall_id', 'ss_mch_id', 'ss_store_id', 'created_at', 'updated_at', 'balance'], 'required']
        ];
    }

}