<?php
namespace app\plugins\baopin\models;

use app\models\BaseActiveRecord;

class BaopinGoods extends BaseActiveRecord{

    public static function tableName(){
        return '{{%plugin_baopin_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(){
        return [
            [['mall_id', 'goods_id', 'created_at', 'updated_at'], 'required'],
        ];
    }

}