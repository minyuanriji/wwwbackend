<?php

namespace app\plugins\alibaba\models;

use app\models\BaseActiveRecord;

class AlibabaDistributionGoodsWarn extends BaseActiveRecord{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_alibaba_distribution_goods_warn}}';
    }

    public function rules(){
        return [
            [['mall_id', 'goods_id', 'created_at', 'updated_at', 'flag'], 'required'],
            [['remark'], 'safe']
        ];
    }

}