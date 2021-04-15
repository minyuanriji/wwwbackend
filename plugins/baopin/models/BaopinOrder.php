<?php


namespace app\plugins\baopin\models;


use app\models\BaseActiveRecord;

class BaopinOrder extends BaseActiveRecord{

    public static function tableName(){
        return '{{%plugin_baopin_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'order_id', 'baopin_id', 'created_at', 'updated_at'], 'required'],
            [['mch_id', 'mch_baopin_id'], 'integer']
        ];
    }

}