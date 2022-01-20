<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\forms\api\goods\ApiGoods;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\models\OrderDetail;

class EcardGoodsListForm extends BaseModel{

    public $order;
    public $orderBy;
    public $keyword;
    public $label;
    public $cat_id;

    public $limit = 10;
    public $page;

    public function rules(){
        return [
            [['cat_id', 'page', 'limit'], 'integer'],
            [['order', 'orderBy', 'keyword', 'label'], 'trim']
        ];
    }

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = Goods::find()->alias('g')->with(["goodsWarehouse", "attr"])
                ->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id")
                ->where(['g.is_delete' => 0,'g.is_recycle' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id]);

            if ($this->keyword) {
                $query->andWhere([
                    "OR",
                    ['LIKE', 'gw.name', str_replace(' ','', $this->keyword)],
                    ['LIKE', 'g.labels', str_replace(' ','', $this->keyword)]
                ]);
            }
            if ($this->label) {
                $query->andWhere([
                    "OR",
                    ['LIKE', 'gw.name', str_replace(' ','', $this->label)],
                    ['LIKE', 'g.labels', str_replace(' ','', $this->label)]
                ]);
            }
            if ($this->cat_id) {
                $catIds = [];
                $catList = GoodsCats::find()->select(["id"])->where(['parent_id' => $this->cat_id, 'is_delete' => 0, 'status' => 1])->all();
                $catIds[] = $this->cat_id;
                if (count($catList) > 0) {
                    foreach ($catList as $cat) {
                        $catIds[] = $cat['id'];
                        $catChildList = GoodsCats::find()->select(["id"])->where(['parent_id' => $cat->id, 'is_delete' => 0, 'status' => 1])->select('id')->asArray()->all();
                        foreach ($catChildList as $c) {
                            $catIds[] = $c['id'];
                        }
                    }
                }
                $query->leftJoin(['gc' => GoodsCatRelation::tableName()], 'gc.goods_warehouse_id=gw.id');
                $query->andWhere(['gc.is_delete'=>0]);
                $query->andWhere(['in', 'gc.cat_id', $catIds]);
            }

            $orderBy = ['g.sort' => SORT_ASC, 'g.id' => SORT_DESC];

            if ($this->order && $this->orderBy) {
                if ($this->order == 'price') {
                    $orderBy = 'g.price ' . $this->orderBy;
                } elseif ($this->order == 'sale') {
                    $orderBy = 'g.virtual_sales ' . $this->orderBy;
                }
            }

            $list = $query->orderBy($orderBy)
                        ->page($pagination, $this->limit, $this->page)->all();
            $newList = [];
            foreach ($list as $item) {

                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 1;

                $detail = $apiGoods->getDetail();
                $detail['use_attr'] = $item->use_attr;
                $detail['unit'] = $item->unit;

                $sales = (int)OrderDetail::find()->where(["goods_id" => $item->id, "is_refund" => 0])->count();
                $detail['sales'] = sprintf("已售%s%s", $sales + $item->virtual_sales, $item->unit);

                $newList[] = $detail;
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $newList ? $newList : [],
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}