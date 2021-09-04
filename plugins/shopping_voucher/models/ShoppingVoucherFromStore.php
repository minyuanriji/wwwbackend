<?php
namespace app\plugins\Shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherFromStore extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_from_store}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'mch_id', 'store_id', 'give_type', 'give_value', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'is_delete'], 'safe']
        ];
    }

}
