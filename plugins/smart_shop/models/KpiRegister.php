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
            [['mall_id', 'inviter_user_id', 'source_user_id', 'user_id_list', 'created_at', 'mobile', 'store_id', 'merchant_id'], 'required'],
            [['point'], 'integer'],
            [['award_data'], 'safe']
        ];
    }
}