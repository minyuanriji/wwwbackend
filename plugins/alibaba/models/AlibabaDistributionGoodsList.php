<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionGoodsList extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_goods_list}}';
    }

    public function rules(){
        return [
            [['mall_id', 'app_id', 'name', 'ali_category_id', 'cover_url', 'ali_offerId', 'price_rate', 'price', 'origin_price_rate', 'origin_price', 'created_at', 'updated_at'], 'required'],
            [['price_rate', 'origin_price_rate'], 'number', 'min' => 100],
            [['is_delete', 'ali_product_info', 'sku_infos'], 'safe']
        ];
    }

}