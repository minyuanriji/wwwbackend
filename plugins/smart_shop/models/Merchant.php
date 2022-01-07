<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class Merchant extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_merchant}}';
    }

    public function rules(){
        return [
            [['mall_id', 'bsh_mch_id', 'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }


}