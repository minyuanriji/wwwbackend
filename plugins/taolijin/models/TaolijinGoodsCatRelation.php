<?php

namespace app\plugins\taolijin\models;

use app\models\BaseActiveRecord;

class TaolijinGoodsCatRelation extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_taolijin_goods_cat_relation}}';
    }

    public function rules(){
        return [
            [['goods_id', 'cat_id'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

}