<?php

namespace app\plugins\perform_distribution\models;

use app\models\BaseActiveRecord;

class Region extends BaseActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%jxmall_plugin_perform_distribution_region}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mall_id', 'name',  'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'integer'],
            [['province_id', 'province', 'city_id', 'city', 'district_id', 'district']]
        ];
    }

}