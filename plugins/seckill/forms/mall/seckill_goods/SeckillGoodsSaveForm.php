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
            [['buy_limit', 'virtual_seckill_num', 'real_stock', 'virtual_stock'], 'required'],
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

        $t = \Yii::$app->db->beginTransaction();
        try {
            if ($this->id) {
                $seckillGoodsModel = SeckillGoods::findOne($this->id);
                if (!$seckillGoodsModel || $seckillGoodsModel->is_delete)
                    throw new \Exception('秒杀商品不存在！');
            } else {
                $seckillGoodsModel = new SeckillGoods();
                $seckillGoodsModel->mall_id = \Yii::$app->mall->id;
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
                    if ($item['id']) {
                        $seckillGoodsPriceModel = SeckillGoodsPrice::findOne($item['id']);
                        if (!$seckillGoodsPriceModel)
                            throw new \Exception($seckillGoodsPriceModel->getErrorMessage());
                    } else {
                        $seckillGoodsPriceModel = new SeckillGoodsPrice();
                        $seckillGoodsPriceModel->mall_id = \Yii::$app->mall->id;
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