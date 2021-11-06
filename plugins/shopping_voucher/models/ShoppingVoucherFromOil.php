<?php

namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherFromOil extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_from_oil}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'plat_id', 'product_id',  'created_at', 'updated_at', 'start_at',
              'first_give_type', 'first_give_value', 'second_give_type', 'second_give_value'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

}