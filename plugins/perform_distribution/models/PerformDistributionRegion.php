<?php

namespace app\plugins\perform_distribution\models;

use app\models\BaseActiveRecord;

class PerformDistributionRegion extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_perform_distribution_region}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'name',  'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'integer'],
            [['address', 'name'], 'trim'],
            [['province_id', 'city_id', 'district_id'], 'integer']
        ];
    }

}