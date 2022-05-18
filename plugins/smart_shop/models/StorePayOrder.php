<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class StorePayOrder extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_store_pay_order}}';
    }

    public function rules(){
        return [
            [['mall_id', 'ss_mch_id', 'ss_store_id', 'business_scene', 'created_at', 'updated_at', 'order_no', 'order_status', 'order_price'], 'required'],
            [['pay_status', 'pay_type', 'pay_price', 'pay_time', 'pay_uid'], 'safe']
        ];
    }

}