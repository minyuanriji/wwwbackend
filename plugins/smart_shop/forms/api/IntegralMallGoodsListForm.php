<?php

namespace app\plugins\smart_shop\forms\api;

use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\api\APICacheDataForm;
use app\forms\api\goods\ApiGoods;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;

class IntegralMallGoodsListForm extends BaseModel implements ICacheForm {

    public $mall_id;
    public $limit = 10;
    public $page;
    public $cat_id;
    public $keyword;

    public function rules(){
        return [
            [['page', 'limit', 'mall_id', 'cat_id'], 'integer'],
            [['keyword'], 'trim']
        ];
    }

    public function getCacheKey(){
        return [(int)$this->page, (int)$this->limit, (int)$this->cat_id, (int)$this->limit, $this->keyword];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        try {
            $catIds = [];
            $catList = GoodsCats::find()->where(['parent_id' => $this->cat_id, 'is_delete' => 0, 'status' => 1])->all();
            $catIds[] = $this->cat_id;
            if (count($catList) > 0) {
                foreach ($catList as $cat) {
                    $catIds[] = $cat['id'];
                    $catChildList = GoodsCats::find()->where(['parent_id' => $cat->id, 'is_delete' => 0, 'status' => 1])->select('id')->asArray()->all();
                    foreach ($catChildList as $c) {
                        $catIds[] = $c['id'];
                    }
                }
            }

            $query = Goods::find()->alias('g')->with(['goodsWarehouse', 'attr'])
                ->where(['g.is_delete' => 0,'g.is_recycle' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id])
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

            if ($this->keyword) {
                $query->keyword($this->keyword, [
                    'or',
                    ['like', 'gw.name', str_replace(' ','',$this->keyword)],
                    ['like', 'g.labels', str_replace(' ','',$this->keyword)]]);
            }

            if ($this->cat_id) {
                $query->leftJoin(['gc' => GoodsCatRelation::tableName()], 'gc.goods_warehouse_id=gw.id');
                $query->andWhere(['gc.is_delete'=>0]);
                $query->andWhere(['in', 'gc.cat_id', $catIds]);
            }

            $orderBy = ['g.sort' => SORT_ASC, 'g.id' => SORT_DESC];
            $list = $query->orderBy($orderBy)->groupBy('g.goods_warehouse_id')->page($pagination, $this->limit, $this->page)->all();
            $newList = [];
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 1;
                $detail = $apiGoods->getDetail();
                $detail['app_share_title'] = $item->app_share_title;
                $detail['app_share_pic']   = $item->app_share_pic;
                $detail['use_attr']        = $item->use_attr;
                $detail['unit']            = $item->unit;
                if ($item->use_virtual_sales) {
                    $detail['sales'] = sprintf("已售%s%s", $item->sales + $item->virtual_sales, $item->unit);
                }
                $newList[] = $detail;
            }

            $sourceData = $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                    'list' => $newList,
                    'page_count' => $pagination->page_count,
                    'total_count' => $pagination->total_count
                ]
            );

            return new APICacheDataForm([
                "sourceData" => $sourceData
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}