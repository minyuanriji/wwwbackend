<?php

namespace app\plugins\seckill\models;

use app\models\BaseActiveRecord;
use app\models\GoodsAttr;

class SeckillGoodsPrice extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_seckill_goods_price}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'goods_id', 'attr_id', 'seckill_id', 'seckill_price', 'seckill_goods_id'], 'required'],
        ];
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'attr_id']);
    }
}