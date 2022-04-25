<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class KpiRegister extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_register}}';
    }

    public function rules(){
        return [
            [['mall_id', 'user_id_list', 'created_at', 'mobile'], 'required'],
        ];
    }
}