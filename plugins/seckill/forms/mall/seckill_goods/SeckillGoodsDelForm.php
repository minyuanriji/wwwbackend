<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;

class SeckillGoodsDelForm extends BaseModel
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
            $seckillGoodsModel = SeckillGoods::findOne($this->id);
            if (!$seckillGoodsModel || $seckillGoodsModel->is_delete)
                throw new \Exception($seckillGoodsModel->getErrorMessage());

            $seckillGoodsModel->is_delete = 1;
            if (!$seckillGoodsModel->save())
                throw new \Exception($seckillGoodsModel->getErrorMessage());

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '删除成功');
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}