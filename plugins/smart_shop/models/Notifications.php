<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class Notifications extends BaseActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_notifications}}';
    }

    public function rules(){
        return [
            [['mall_id',  'ss_mch_id', 'ss_store_id', 'created_at', 'updated_at', 'type'], 'required'],
            [['enable'], 'integer'],
            [['data_json'], 'trim']
        ];
    }

}