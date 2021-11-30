<?php

namespace app\plugins\addcredit\models;

use app\models\BaseActiveRecord;

class AddcreditPlateforms extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_addcredit_plateforms}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'sdk_dir', 'created_at', 'updated_at', 'class_dir', 'json_param', 'ratio', 'transfer_rate'], 'required'],
            [['is_enabled', 'product_json_data', 'enable_fast', 'enable_slow', 'pattern_deny', 'allow_plats', 'parent_id'], 'safe']
        ];
    }
}




