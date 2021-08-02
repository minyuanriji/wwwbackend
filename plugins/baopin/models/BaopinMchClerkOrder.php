<?php
namespace app\plugins\baopin\models;


use app\models\BaseActiveRecord;

class BaopinMchClerkOrder extends BaseActiveRecord{

    public static function tableName(){
        return '{{%plugin_baopin_mch_clerk_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'order_id', 'goods_id', 'created_at', 'updated_at', 'mch_id', 'store_id'], 'required'],
            [['deleted_at', 'is_delete'], 'integer']
        ];
    }
}