<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class KpiLinkCoupon extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_link_coupon}}';
    }

    public function rules(){
        return [
            [['mall_id', 'inviter_user_id', 'user_id_list', 'created_at', 'mobile', 'date', 'store_id', 'merchant_id'], 'required'],
            [['point'], 'integer']
        ];
    }
}