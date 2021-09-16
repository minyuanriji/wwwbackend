<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionGoodsSku extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_goods_sku}}';
    }

    public function rules(){
        return [
            [['mall_id', 'goods_id', 'ali_sku_id', 'ali_attributes', 'ali_spec_id', 'price', 'origin_price', 'created_at', 'updated_at'], 'required'],
            [['cargo_number', 'amount_on_sale', 'ali_price', 'consign_price', 'is_delete'], 'safe']
        ];
    }


}