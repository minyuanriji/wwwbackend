<?php
namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherFromGiftpacks extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_from_giftpacks}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'pack_id', 'give_type', 'give_value', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'is_delete', 'start_at', 'recommender'], 'safe']
        ];
    }

}
