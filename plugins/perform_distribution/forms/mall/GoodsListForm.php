<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\perform_distribution\models\PerformDistributionGoods;

class GoodsListForm extends BaseModel{

    public $keyword;
    public $limit = 10;
    public $page = 1;

    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
            [['limit', 'page'], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $query = PerformDistributionGoods::find()->alias('pdg')
                ->where(['pdg.is_delete' => 0, 'pdg.mall_id' => \Yii::$app->mall->id])
                ->leftJoin(['g' => Goods::tableName()], 'g.id = pdg.goods_id')
                ->leftJoin(['w' => GoodsWarehouse::tableName()], 'w.id=g.goods_warehouse_id');

            if ($this->keyword) {
                $query->andWhere(['like', 'w.name', $this->keyword]);
            }

            $list = $query->select('w.name,w.cover_pic,pdg.*')
                ->page($pagination, $this->limit, $this->page)
                ->orderBy("pdg.id DESC")->asArray()->all();

            foreach ($list as $key => $item) {
                $goods = Goods::findOne($item['goods_id']);
                $item['goods'] = [
                    "name"           => $goods->goodsWarehouse->name,
                    "price"          => $goods->price,
                    "profit_price"   => $goods->profit_price,
                    "cover_pic"      => $goods->goodsWarehouse->cover_pic,
                    "original_price" => $goods->goodsWarehouse->original_price
                ];
                $list[$key] = $item;
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'list'       => $list,
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }
}