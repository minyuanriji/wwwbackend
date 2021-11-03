<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;

class SeckillGoodsSkuSearchForm extends BaseModel
{

    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'integer'],
        ];
    }

    /**
     * @Note: 获取商城商品详情及规格
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $goodsInfo = Goods::find()->where(['id' => $this->goods_id, 'is_delete' => 0])->with('goodsWarehouse','attr')->asArray()->one();
            if (empty($goodsInfo) || !$goodsInfo['status'])
                throw new \Exception('商品不存在或已下架');

            $info = [];
            $goodsInfo['attr_groups'] = json_decode($goodsInfo['attr_groups'], true);
            $info['buy_limit'] = 0;
            $info['cover_pic'] = $goodsInfo['goodsWarehouse']['cover_pic'];
            $info['goods_id'] = $goodsInfo['id'];
            $info['id'] = 0;
            $info['mall_id'] = $goodsInfo['mall_id'];
            $info['name'] = $goodsInfo['goodsWarehouse']['name'];
            $info['real_stock'] = 0;
            $info['original_price'] = $goodsInfo['goodsWarehouse']['original_price'];
            $info['virtual_seckill_num'] = 0;
            $info['virtual_stock'] = 0;
            $info['seckillGoodsPrice'] = [];
            foreach ($goodsInfo['attr'] as $attr) {
                $sign_id = explode(':', $attr['sign_id']);
                foreach ($sign_id as $signKey => $signId) {
                    if (isset($goodsInfo['attr_groups'][$signKey])) {
                        $attr_list = array_combine(array_column($goodsInfo['attr_groups'][$signKey]['attr_list'], 'attr_id'), $goodsInfo['attr_groups'][$signKey]['attr_list']);
                        $spec_name[$signKey]['key'] = $goodsInfo['attr_groups'][$signKey]['attr_group_name'];
                        $spec_name[$signKey]['val'] = isset($attr_list[$signId]) ? $attr_list[$signId]['attr_name'] : '';
                    }
                }
                $attrInfo['attr_id'] = $attr['id'];
                $attrInfo['spec_name'] = $spec_name;
                $attrInfo['goods_id'] = $goodsInfo['id'];
                $attrInfo['score_deduction_price'] = 0;
                $attrInfo['seckill_price'] = 0;
                $attrInfo['attr_price'] = $attr['price'];
                $info['seckillGoodsPrice'][] = $attrInfo;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [$info]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}