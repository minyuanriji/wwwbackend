<?php
namespace app\plugins\baopin\models;


use app\models\BaseActiveRecord;

class BaopinMchGoods extends BaseActiveRecord{

    public static function tableName(){
        return '{{%plugin_baopin_mch_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mch_id', 'mch_id', 'store_id', 'goods_id', 'created_at', 'updated_at'], 'required'],
            [['sort', 'is_delete'], 'integer']
        ];
    }

}