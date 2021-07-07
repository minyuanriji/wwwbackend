<?php
namespace app\models;


class EfpsMerchantMcc extends BaseActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%efps_merchant_mcc}}';
    }

    public function rules(){
        return [
            [['type', 'code', 'name'], 'required'],
            [['code'], 'integer']
        ];
    }

}