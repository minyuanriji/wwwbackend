<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\plugins\perform_distribution\models\PerformDistributionGoods;

class GoodsEditForm extends BaseModel{

    public $id;
    public $goods_id;

    public function rules(){
        return [
            [['goods_id'], 'required'],
            [['goods_id', 'id'], 'integer']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $goods = Goods::findOne($this->goods_id);
            if(!$goods || $goods->is_delete || $goods->is_recycle || $goods->status != Goods::STATUS_ON){
                throw new \Exception("商品不存在或已下架");
            }

            $performDistributionGoods = PerformDistributionGoods::findOne(["goods_id" => $this->goods_id]);
            if(!$performDistributionGoods){
                $performDistributionGoods = new PerformDistributionGoods([
                    "mall_id"    => $goods->mall_id,
                    "goods_id"   => $goods->id,
                    "created_at" => time()
                ]);
            }
            $performDistributionGoods->updated_at = time();
            $performDistributionGoods->is_delete  = 0;
            if(!$performDistributionGoods->save()){
                throw new \Exception($this->responseErrorMsg($performDistributionGoods));
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}