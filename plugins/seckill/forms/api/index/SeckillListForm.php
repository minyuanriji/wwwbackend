<?php

namespace app\plugins\seckill\forms\api\index;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderDetail;
use app\plugins\seckill\models\Seckill;

class SeckillListForm extends BaseModel
{
    public $type;

    public function rules()
    {
        return [
            [['type'], 'string']
        ];
    }

    public function list()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try{
            /*$beforeDawn = strtotime(date('Y-m-d', time()));//凌晨0:0
            $night = strtotime(date('Y-m-d',strtotime('+1 day'))) - 1;//23:59*/

            $seckill = Seckill::find()->andWhere([
                'and',
                ['<', 'start_time', time()],
                ['>', 'end_time', time()],
                ['is_delete' => 0],
                ['mall_id' => \Yii::$app->mall->id],
            ])->with(
                'seckillGoods',
                'seckillGoods.seckillGoodsPrice',
                'seckillGoods.goods',
                'seckillGoods.goods.goodsWarehouse'
            )->select('id,name,start_time,end_time,pic_url')->asArray()->one();

            if ($seckill) {
                foreach ($seckill['seckillGoods'] as &$item) {
                    $item['cover_pic'] = $item['goods']['goodsWarehouse']['cover_pic'] ?? '';
                    $item['name'] = $item['goods']['goodsWarehouse']['name'] ?? '';
                    $item['original_price'] = $item['goods']['goodsWarehouse']['original_price'] ?? '';
                    array_multisort(array_column($item['seckillGoodsPrice'],'score_deduction_price'),SORT_ASC,$item['seckillGoodsPrice']);
                    $item['score_deduction_price'] = $item['seckillGoodsPrice'][0]['score_deduction_price'] ?? 0;
                    $item['seckill_price'] = $item['seckillGoodsPrice'][0]['seckill_price'] ?? 0;
                    unset($item['goods'], $item['seckillGoodsPrice']);

                    $query = Order::find()->alias('o');

                    $item['buyNum'] = $query->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id=o.id')
                        ->andWhere([
                            'and',
                            ['o.cancel_status' => 0],
                            ['od.goods_id' => $item['goods_id']],
                            ['>', 'o.created_at', $seckill['start_time']],
                            ['<', 'o.created_at', $seckill['end_time']],
                        ])->sum('od.num') ?: 0;

                }
                $seckill['start_time'] = date('Y-m-d', $seckill['start_time']);
                $seckill['end_time'] = date('Y-m-d H:i:s', $seckill['end_time']);
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', $seckill);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}