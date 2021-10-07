<?php
namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherFromHotel extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_from_hotel}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'hotel_id', 'give_type', 'give_value', 'created_at', 'updated_at'], 'required'],
            [['deleted_at', 'is_delete', 'start_at'], 'safe']
        ];
    }

}
