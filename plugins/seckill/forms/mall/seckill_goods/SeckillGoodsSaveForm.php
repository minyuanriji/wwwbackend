<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use app\plugins\seckill\models\SeckillGoodsPrice;

class SeckillGoodsSaveForm extends BaseModel
{

    public $id;
    public $seckill_id;
    public $goods_id;
    public $buy_limit;
    public $virtual_seckill_num;
    public $real_stock;
    public $virtual_stock;
    public $seckillGoodsPrice;

    public function rules()
    {
        return [
            [['buy_limit', 'virtual_seckill_num', 'real_stock', 'virtual_stock', 'goods_id', 'seckill_id'], 'required'],
            [['id', 'seckill_id', 'goods_id'], 'integer'],
            [['seckillGoodsPrice'], 'safe']
        ];
    }

    /**
     * @Note: 秒杀商品保存、编辑
     * @return array
     */

    public function save()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        if ($this->virtual_stock < $this->real_stock) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '虚拟库存不能小于真实库存！');
        }

        //判断活动是否结束
        $seckill = Seckill::findOne($this->seckill_id);
        if (!$seckill || $seckill->is_delete)
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '该秒杀活动不存在！');

        if ($seckill->end_time < time())
            return $this->returnApiResultData(ApiCode::CODE_FAIL, '该秒杀活动已结束！');

        //判断该商品是否在某个秒杀活动进行中
        $seckillGoodsModel = SeckillGoods::find()->andWhere(['goods_id' => $this->goods_id, 'is_delete' => 0])->asArray()->all();
        if ($seckillGoodsModel && !$this->id) {
            $seckill_ids = array_column($seckillGoodsModel, 'seckill_id');

            $seckillModel = Seckill::find()->andWhere([
                'and',
                ['in', 'id', $seckill_ids],
                ['>', 'end_time', time()],
            ])->count();
            if ($seckillModel > 0)
                return $this->returnApiResultData(ApiCode::CODE_FAIL, '该商品在其它秒杀活动进行中,请选择其他商品！');
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $seckillGoodsModel = SeckillGoods::findOne($this->id);
                if (!$seckillGoodsModel || $seckillGoodsModel->is_delete)
                    throw new \Exception('秒杀商品不存在！');
            } else {
                $seckillGoodsModel = SeckillGoods::find()->andWhere(['seckill_id' => $this->seckill_id, 'goods_id' => $this->goods_id])->one();
                if (!$seckillGoodsModel) {
                    $seckillGoodsModel = new SeckillGoods();
                    $seckillGoodsModel->mall_id = \Yii::$app->mall->id;
                } else {
                    $seckillGoodsModel->is_delete = 0;
                }
            }
            $seckillGoodsModel->seckill_id = $this->seckill_id;
            $seckillGoodsModel->goods_id = $this->goods_id;
            $seckillGoodsModel->buy_limit = $this->buy_limit;
            $seckillGoodsModel->virtual_seckill_num = $this->virtual_seckill_num;
            $seckillGoodsModel->real_stock = $this->real_stock;
            $seckillGoodsModel->virtual_stock = $this->virtual_stock;
            if (!$seckillGoodsModel->save())
                throw new \Exception($seckillGoodsModel->getErrorMessage());

            if ($this->seckillGoodsPrice) {
                foreach ($this->seckillGoodsPrice as $item) {
                    if ($item['attr_price'] < $item['score_deduction_price']) {
                        throw new \Exception('积分抵扣金额不能大于原价格，规格ID：' . $item['attr_id']);
                    }

                    if ($item['id']) {
                        $seckillGoodsPriceModel = SeckillGoodsPrice::findOne($item['id']);
                        if (!$seckillGoodsPriceModel)
                            throw new \Exception($seckillGoodsPriceModel->getErrorMessage());
                    } else {
                        $seckillGoodsPriceModel = SeckillGoodsPrice::find()->andWhere(['goods_id' => $item['goods_id'], 'attr_id' => $item['attr_id'], 'seckill_id' => $item['seckill_id'] ?? $this->seckill_id])->one();
                        if (!$seckillGoodsPriceModel) {
                            $seckillGoodsPriceModel = new SeckillGoodsPrice();
                            $seckillGoodsPriceModel->mall_id = \Yii::$app->mall->id;
                        }
                    }
                    $seckillGoodsPriceModel->goods_id = $item['goods_id'];
                    $seckillGoodsPriceModel->attr_id = $item['attr_id'];
                    $seckillGoodsPriceModel->seckill_id = $item['seckill_id'] ?? $this->seckill_id;
                    $seckillGoodsPriceModel->seckill_price = $item['seckill_price'];
                    $seckillGoodsPriceModel->score_deduction_price = $item['score_deduction_price'];
                    $seckillGoodsPriceModel->seckill_goods_id = $item['seckill_goods_id'] ?? $seckillGoodsModel->id;
                    if (!$seckillGoodsPriceModel->save())
                        throw new \Exception($seckillGoodsPriceModel->getErrorMessage());
                }
            }

            $t->commit();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '保存成功');
        } catch (\Exception $e) {
            $t->rollBack();
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}