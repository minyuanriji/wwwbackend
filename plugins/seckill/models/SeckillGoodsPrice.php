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
            [['mall_id', 'goods_id', 'attr_id', 'seckill_id', 'seckill_goods_id'], 'required'],
            [['shopping_voucher_deduction_price', 'seckill_price', 'score_deduction_price'], 'safe']
        ];
    }

    public function getGoodsAttr()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'attr_id']);
    }
}