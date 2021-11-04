<?php

namespace app\plugins\oil\models;

use app\models\BaseActiveRecord;

class OilPlateforms extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_plateforms}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'sdk_src', 'created_at', 'updated_at'], 'required'],
            [['is_delete', 'region_deny', 'is_enabled', 'product_json_data'], 'safe']
        ];
    }

}