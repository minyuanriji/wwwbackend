<?php
namespace app\plugins\mch\forms\api;

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
use app\models\Store;
use app\plugins\mch\models\Mch;

class MchGoodsListForm extends BaseModel implements ICacheForm {

    public $page;
    public $store_id;
    public $mall_id;

    public function rules(){
        return [
            [['store_id'], 'required'],
            [['page'], 'default', 'value' => 1],
            [['mall_id'], 'safe']
        ];
    }

    public function tmp(){
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }

        $catIds = [];
        if($this->cat_id){
            $catList = GoodsCats::find()->select(["id"])->where([
                'parent_id' => $this->cat_id,
                'is_delete' => 0,
                'status'    => 1
            ])->all();
            $catIds[] = $this->cat_id;
            if ($catList) {
                foreach ($catList as $cat) {
                    $catIds[] = $cat->id;
                    $catChildList = GoodsCats::find()->where([
                        'parent_id' => $cat->id,
                        'is_delete' => 0,
                        'status'    => 1
                    ])->select('id')->asArray()->all();
                    if($catChildList){
                        foreach ($catChildList as $c) {
                            $catIds[] = $c['id'];
                        }
                    }

                }
            }
        }

        try {
            $query = Goods::find()->alias('g')
                                  ->with(['goodsWarehouse', 'attr'])
                                  ->where([
                                       'g.is_delete' => 0,
                                       'g.is_recycle' => 0,
                                       'g.status' => 1,
                                       'g.mall_id' => \Yii::$app->mall->id,
                                       'g.mch_id' => $this->mch_id
                                  ])->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');

            if ($this->keyword) {
                $query->keyword($this->keyword, [
                    'or',
                    ['like', 'gw.name', $this->keyword],
                    ['like', 'g.labels', $this->keyword]]);
            }
            if ($this->label) {
                $query->keyword($this->label, [
                    'or',
                    ['like', 'gw.name', $this->label],
                    ['like', 'g.labels', $this->label]]);
            }
            if ($this->cat_id) {
                $query->leftJoin(['gc' => GoodsCatRelation::tableName()], 'gc.goods_warehouse_id=gw.id');
                $query->andWhere(['gc.is_delete'=>0]);
                if($this->keyword){
                    $query->orWhere(['in', 'gc.cat_id', $catIds]);
                }else{
                    $query->andWhere(['in', 'gc.cat_id', $catIds]);
                }
            }
            /**
             * @var BasePagination $pagination
             */
            $list = $query->orderBy(['g.sort' => SORT_ASC, 'g.id' => SORT_DESC])
                ->groupBy('g.goods_warehouse_id')
                ->page($pagination, $this->limit, $this->page)
                ->all();
            $newList = [];
            /* @var Goods[] $list */
            foreach ($list as $item) {
                $apiGoods = ApiGoods::getCommon();
                $apiGoods->goods = $item;
                $apiGoods->isSales = 1;
                $detail = $apiGoods->getDetail();
                $detail['app_share_title'] = $item->app_share_title;
                $detail['app_share_pic'] = $item->app_share_pic;
                $detail['use_attr'] = $item->use_attr;
                $detail['unit'] = $item->unit;
                if ($item->use_virtual_sales) {
                    $detail['sales'] = sprintf("已售%s%s", $item->sales + $item->virtual_sales, $item->unit);
                }
                $newList[] = $detail;
            }
            return $this->returnApiResultData(
                ApiCode::CODE_SUCCESS,
                '',
                [
                    'list' => $newList,
                    'page_count' => $pagination->page_count,
                    'total_count' => $pagination->total_count
                ]);
        } catch (\Exception $exception) {
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $exception->getMessage());

        }
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $store = Store::findOne($this->store_id);
            if(!$store || $store->is_delete){
                throw new \Exception("门店不存在");
            }

            $mch = Mch::findOne($store->mch_id);
            if(!$mch || $mch->is_delete || $mch->review_status){

            }

            $query = Goods::find()->alias('g');
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->andWhere([
                'g.is_delete' => 0,
                'g.is_recycle' => 0,
                'g.status'     => 1,
                'g.mall_id'    => $this->mall_id,
                'g.mch_id'     => $this->store_id
            ]);
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey(){
        return ["store_id" => $this->store_id, "page" => $this->page];
    }
}