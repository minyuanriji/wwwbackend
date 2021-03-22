<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * Created by PhpStorm
 * Author: ganxiaohao
 * Date: 2020-04-28
 * Time: 11:51
 */

namespace app\forms\api\goods;

namespace app\forms\api\goods;

use app\core\ApiCode;
use app\core\BasePagination;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsCatRelation;
use app\models\GoodsCats;
use app\models\GoodsWarehouse;
use app\logic\OptionLogic;
use app\models\Option;
use app\models\mysql\GoodsCatRelation as GoodsCatRelationModel;
use function Webmozart\Assert\Tests\StaticAnalysis\false;

class GoodsListForm extends BaseModel
{
    public $page;
    public $limit;
    public $cat_id;
    public $keyword;
    public $label;

    public function rules()
    {
        return [
            [['page', 'limit', 'cat_id'], 'integer'],
            [['keyword', 'label'], 'string'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 10],
        ];
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->returnApiResultData();
        }
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
        try {
            $query = Goods::find()
                ->alias('g')
                ->with(['goodsWarehouse', 'attr'])
                ->where(['g.is_delete' => 0, 'g.status' => 1, 'g.mall_id' => \Yii::$app->mall->id,])
                ->leftJoin(['gw' => GoodsWarehouse::tableName()], 'gw.id=g.goods_warehouse_id');
            if ($this->keyword) {
                $query->keyword($this->keyword, [
                    'or',
                    ['like', 'gw.name', str_replace(' ','',$this->keyword)],
                    ['like', 'g.labels', str_replace(' ','',$this->keyword)]]);
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
                -> all();

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

            if(!empty($this -> cat_id)){
                $GoodsCatRelation = new GoodsCatRelationModel();
                foreach ($newList as $key => $val){
                    if($val['is_delete'] !== 1 && $val['status'] !== 0){
                        $GoodsCat = $GoodsCatRelation -> getGoodsCatId($val['goods_warehouse_id']);
                        if(!$this -> deep_in_array($this -> cat_id,$GoodsCat)){
                            unset($newList[$key]);
                        }
                    }else{
                        unset($newList[$key]);
                    }
                }
                $newList = array_merge($newList);
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
     * @param $cat_id  查找的值  2
     * @param $data 数组
     * @return bool
     */
    public function deep_in_array($cat_id,$data){
        foreach ($data as $key => $val){
            if(array_search($cat_id,$val)){
                return true;
            }else{
                continue;
            }
        }
        return false;
    }

}
