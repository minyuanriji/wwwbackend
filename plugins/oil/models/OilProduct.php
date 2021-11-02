<?php

namespace app\plugins\oil\models;

use app\models\BaseActiveRecord;

class OilProduct extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_oil_product}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'plat_id', 'name', 'price', 'created_at', 'updated_at'], 'required'],
            [['status', 'is_delete'], 'safe']
        ];
    }
}