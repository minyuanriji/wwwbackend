<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class MerchantFzlist extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_merchant_fzlist}}';
    }

    public function rules(){
        return [
            [['mall_id', 'bsh_mch_id', 'ss_mch_id', 'ss_store_id', 'name', 'logo', 'mobile'], 'required'],
            [['address', 'is_delete'], 'safe']
        ];
    }

}