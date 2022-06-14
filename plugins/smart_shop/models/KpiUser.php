<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class KpiUser extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_user}}';
    }

    public function rules(){
        return [
            [['mall_id', 'ss_mch_id', 'ss_store_id', 'created_at', 'updated_at', 'realname', 'mobile'], 'required'],
            [['is_delete'], 'safe']
        ];
    }
}