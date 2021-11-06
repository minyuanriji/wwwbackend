<?php

namespace app\plugins\seckill\forms\mall\seckill_goods;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;

class MallGoodsSearchForm extends BaseModel
{
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['keyword'], 'string'],
        ];
    }

    /**
     * @Note: 获取商城商品
     * @return array
     */

    public function search()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $query = Goods::find()->andWhere([
                'and',
                ['mall_id' => \Yii::$app->mall->id],
                ['status' => 1],
                ['is_delete' => 0],
//                ['>', 'forehead_score', 0]
            ])->with(['goodsWarehouse' => function ($query) {
                $query->select('id,cover_pic,name,original_price');
            }]);
            if ($this->keyword) {
               $goodsWarehouseId =  GoodsWarehouse::find()->andWhere(['like', 'name', $this->keyword])->select('id');
                $query->andWhere([
                    'or',
                    ['id' => $this->keyword],
                    ['goods_warehouse_id' => $goodsWarehouseId],
                ]);
            }
            $list = $query->select('id,goods_stock,forehead_score,status,goods_warehouse_id')
                ->page($pagination, 10, $this->page)
                ->orderBy(['id' => SORT_DESC])
                ->asArray()->all();
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list' => $list,
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}