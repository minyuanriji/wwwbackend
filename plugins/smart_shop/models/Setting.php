<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class Setting extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_setting}}';
    }

    public function rules(){
        return [
            [['mall_id', 'key', 'value'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

}