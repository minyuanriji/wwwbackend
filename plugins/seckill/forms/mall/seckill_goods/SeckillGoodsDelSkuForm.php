<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;
use app\plugins\seckill\models\SeckillGoodsPrice;

class SeckillGoodsDelSkuForm extends BaseModel
{

    public $id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
        ];
    }

    /**
     * @Note: 删除秒杀商品
     * @return array
     */

    public function delete()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $seckillGoodsPriceModel = SeckillGoodsPrice::findOne($this->id);
            if (!$seckillGoodsPriceModel)
                throw new \Exception($seckillGoodsPriceModel->getErrorMessage());

            $seckillCount = SeckillGoodsPrice::find()->count();
            if ($seckillCount <= 1)
                throw new \Exception('不能全部删除规格');

            if (!$seckillGoodsPriceModel->delete())
                throw new \Exception($seckillGoodsPriceModel->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}