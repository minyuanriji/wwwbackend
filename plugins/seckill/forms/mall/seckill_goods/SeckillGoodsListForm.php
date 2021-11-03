<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\seckill\models\Seckill;
use app\plugins\seckill\models\SeckillGoods;

class SeckillGoodsListForm extends BaseModel
{

    public $page;
    public $keyword;
    public $keyword_type;


    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword', 'keyword_type'], 'string'],
        ];
    }

    /**
     * @Note: 获取秒杀商品
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = SeckillGoods::find()->alias('sg')->where([
                'sg.mall_id' => \Yii::$app->mall->id,
                'sg.is_delete' => 0
            ])->with('seckillGoodsPrice', 'seckillGoodsPrice.goodsAttr', 'seckill');

            $query->leftJoin(['g' => Goods::tableName()], 'sg.goods_id=g.id');
            $query->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

            $query->keyword($this->keyword_type == 'goods_name', ['like', 'gw.name', $this->keyword]);

            $list = $query->select('sg.*,gw.name,gw.cover_pic,gw.original_price,g.attr_groups')
                ->page($pagination)
                ->orderBy(['id' => SORT_ASC])
                ->asArray()->all();

            if ($list) {
                foreach ($list as &$item) {
                    $item['attr_groups'] = json_decode($item['attr_groups'], true);
                    foreach ($item['seckillGoodsPrice'] as &$seckillGoodsPrice) {
                        if (isset($seckillGoodsPrice['goodsAttr'])) {
                            $seckillGoodsPrice['sign_id'] = explode(':', $seckillGoodsPrice['goodsAttr']['sign_id']);
                            foreach ($seckillGoodsPrice['sign_id'] as $signKey => $signId) {
                                if (isset($item['attr_groups'][$signKey])) {
                                    $attr_list = array_combine(array_column($item['attr_groups'][$signKey]['attr_list'], 'attr_id'), $item['attr_groups'][$signKey]['attr_list']);
                                    $seckillGoodsPrice['spec_name'][$signKey]['key'] = $item['attr_groups'][$signKey]['attr_group_name'];
                                    $seckillGoodsPrice['spec_name'][$signKey]['val'] = isset($attr_list[$signId]) ? $attr_list[$signId]['attr_name'] : '';
                                }
                            }
                            $seckillGoodsPrice['attr_price'] = $seckillGoodsPrice['goodsAttr']['price'];
                        }
                    }
                    unset($item['attr_groups']);
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list,
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}