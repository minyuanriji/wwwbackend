<?php

namespace app\plugins\oil\models;

use app\models\BaseActiveRecord;

class OilSetting extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_setting}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'name', 'value', 'created_at', 'updated_at'], 'required'],
            [[], 'safe']
        ];
    }
}