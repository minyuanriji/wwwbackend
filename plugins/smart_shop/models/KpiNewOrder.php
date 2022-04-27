<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class KpiNewOrder extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_new_order}}';
    }

    public function rules(){
        return [
            [['mall_id', 'user_id_list', 'created_at', 'mobile', 'store_id', 'merchant_id', 'source_table', 'source_id'], 'required'],
        ];
    }
}