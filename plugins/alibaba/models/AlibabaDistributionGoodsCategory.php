<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionGoodsCategory extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_goods_category}}';
    }

    public function rules(){
        return [
            [['mall_id', 'name', 'ali_cat_id', 'ali_parent_id', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'orgin_data', 'sort', 'cover_url'], 'safe']
        ];
    }
}