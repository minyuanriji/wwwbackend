<?php

namespace app\plugins\seckill\models;

use app\models\BaseActiveRecord;
use app\models\Goods;

class SeckillGoods extends BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plugin_seckill_goods}}';
    }

    public function rules()
    {
        return [
            [['mall_id', 'seckill_id', 'goods_id', 'buy_limit', 'virtual_seckill_num', 'real_stock', 'virtual_stock'], 'required'],
            [['is_delete'], 'safe']
        ];
    }

    public function getSeckillGoodsPrice()
    {
        return $this->hasMany(SeckillGoodsPrice::className(), ['seckill_goods_id' => 'id']);
    }

    public function getSeckill()
    {
        return $this->hasOne(Seckill::className(), ['id' => 'seckill_id']);
    }
}