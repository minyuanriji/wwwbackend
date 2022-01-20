<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class AlibabaShoppingVoucherGoods extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_alibaba_shopping_voucher_goods}}';
    }

    public function rules(){
        return [
            [['mall_id', 'alibaba_goods_id', 'ss_store_id', 'created_at', 'updated_at'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

}