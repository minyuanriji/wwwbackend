<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionOrderDetail1688 extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order_detail_1688}}';
    }

    public function rules(){
        return [
            [['mall_id', 'app_id', 'order_id', 'order_detail_id', 'goods_id', 'user_id',
              'ali_total_amount', 'ali_order_id', 'ali_post_fee', 'ali_postdata', 'created_at',
              'updated_at', 'app_key', 'status'], 'required'],
            [['do_error', 'try_count'], 'safe']
        ];
    }

}