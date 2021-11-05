<?php

namespace app\plugins\seckill\models;

use app\core\ApiCode;
use app\models\BaseActiveRecord;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;

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

    public function getGoods ()
    {
        return $this->hasOne(Goods::className(), ['id' => 'goods_id']);
    }

    //判断商品是否是秒杀商品，是否在活动时间内
    public static function judgeSeckillGoods ($goods_id)
    {
        $time = time();

        $query = self::find()->alias('sg');

        $query->leftJoin(['s' => Seckill::tableName()], 's.id=sg.seckill_id')
            ->andWhere([
                'and',
                ['s.is_delete' => 0],
                ['<', 's.start_time', $time],
                ['>', 's.end_time', $time],
            ]);

        $exist = $query->select('sg.*, s.start_time, s.end_time')
            ->andWhere(['sg.is_delete' => 0, 'sg.goods_id' => $goods_id])->with('seckillGoodsPrice')->asArray()->one();

        return $exist;
    }

    public static function SeckillGoodsBuyNum ($goods_id, $seckillResult, $user_id=0)
    {
        $query = Order::find()->alias('o');

        if ($user_id) {
            $query->andWhere(['o.user_id' => $user_id]);
        }

        $buyNum = $query->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id=o.id')
            ->andWhere([
                'and',
                ['o.cancel_status' => 0],
                ['od.goods_id' => $goods_id],
                ['>', 'o.created_at', $seckillResult['start_time']],
                ['<', 'o.created_at', $seckillResult['end_time']],
            ])->sum('od.num') ?: 0;
        return $buyNum;
    }
}