<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionOrderException extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_order_exception}}';
    }

    public function rules(){
        return [
            [['mall_id', 'order_id'], 'required'],
            [['content', 'order_detail_id', 'order_detail_1688_id'], 'safe']
        ];
    }

}