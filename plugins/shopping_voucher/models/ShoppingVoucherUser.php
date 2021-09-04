<?php

namespace app\plugins\shopping_voucher\models;

use app\models\BaseActiveRecord;

class ShoppingVoucherUser extends BaseActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_shopping_voucher_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'user_id', 'money', 'created_at', 'updated_at'], 'required']
        ];
    }






}