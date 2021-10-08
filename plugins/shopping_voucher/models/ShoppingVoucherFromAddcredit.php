<?php
namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherFromAddcredit extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_from_addcredit}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'sdk_key', 'created_at', 'updated_at'], 'required'],
            [['param_data_json', 'is_delete'], 'safe']
        ];
    }

}
