<?php

namespace app\plugins\smart_shop\models;

use app\models\BaseActiveRecord;

class KpiLinkGoods extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName(){
        return '{{%plugin_smartshop_kpi_link_goods}}';
    }

    public function rules(){
        return [
            [['mall_id', 'user_id_list', 'created_at', 'mobile', 'goods_id', 'date'], 'required'],
        ];
    }
}