<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionOrderRefund extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order_refund}}';
    }

    public function rules(){
        return [
            [['mall_id', 'order_id', 'order_detail_id', 'user_id', 'status', 'refund_amount', 'refund_type', 'created_at', 'updated_at'], 'required'],
            [['real_amount', 'remark'], 'safe']
        ];
    }

}