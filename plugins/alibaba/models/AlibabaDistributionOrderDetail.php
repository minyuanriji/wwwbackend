<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionOrderDetail extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order_detail}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'order_id', 'sku_id', 'ali_sku', 'goods_id', 'num', 'unit_price', 'total_original_price', 'total_price', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'deleted_at', 'is_refund', 'refund_status'], 'integer'],
            [['shopping_voucher_decode_price', 'shopping_voucher_num'], 'number', 'min' => 0],
            [['sku_labels'], 'safe']
        ];
    }

}