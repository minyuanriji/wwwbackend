<?php
/**
 * @link:http://www.gdqijianshi.com/
 * @copyright: Copyright (c) 2020 广东七件事集团
 * 文件描述
 * Author: xuyaoxiang
 * Date: 2020/9/28
 * Time: 16:00
 */

/**
 * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

namespace app\plugins\group_buy\forms\mall;

use app\core\ApiCode;
use app\core\BasePagination;
use app\forms\mall\export\MallGoodsExport;
use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\mch\models\MchGoods;
use yii\helpers\ArrayHelper;
use app\plugins\group_buy\models\PluginGroupBuyGoods;

class GoodsListForm extends BaseGoodsList
{
    public $choose_list;
    public $flag;

    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query)
    {
        $query->andWhere([
            'g.sign'   => \Yii::$app->admin->identity->mch_id > 0 ? 'mch' : '',
            'g.mch_id' => \Yii::$app->admin->identity->mch_id,
        ])->with('mallGoods');

        if (\Yii::$app->admin->identity->mch_id > 0) {
            $query->with('mchGoods', 'goodsWarehouse.mchCats');
        }

        if ($this->flag == "EXPORT") {
            if ($this->choose_list && count($this->choose_list) > 0) {
                $query->andWhere(['g.id' => $this->choose_list]);
            }
            $new_query = clone $query;
            $exp       = new MallGoodsExport();
            $res       = $exp->export($new_query);
            return $res;
        }

        return $query;
    }

    public function getList()
    {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }
        try {
            $search = \Yii::$app->serializer->decode($this->search);
        } catch (\Exception $exception) {
            $search = [];
        }

        /** @var Goods $model */
        $model = $this->goodsModel;
        $query = $model::find()->alias('g')->where([
            'g.mall_id'   => \Yii::$app->mall->id,
            'g.is_delete' => 0,
        ]);

        $query->leftJoin(['gbg' => PluginGroupBuyGoods::tableName()], 'gbg.goods_id=g.id');

        $group_buy_goods_list = PluginGroupBuyGoods::find()->where(['deleted_at' => 0, 'mall_id' => \Yii::$app->mall->id])->select('goods_id')->all();

        $group_buy_goods_list = array_column($group_buy_goods_list, 'goods_id');

        $query->andWhere(['not in', 'g.id', $group_buy_goods_list]);

        // 商品名称搜索
        if (isset($search['keyword']) && $search['keyword']) {
            $keyword  = trim($search['keyword']);
            $goodsIds = GoodsWarehouse::find()->andWhere(['is_delete' => 0])
                ->keyword($keyword, ['LIKE', 'name', $keyword])->select('id');
            $query->andWhere([
                'or',
                ['like', 'g.id', $search['keyword']],
                ['g.goods_warehouse_id' => $goodsIds]
            ]);
        }
        // 商品排序
        if (isset($search['sort_prop']) && $search['sort_prop'] && isset($search['sort_type'])) {
            $sortType = $search['sort_type'] ? SORT_ASC : SORT_DESC;
            if ($search['sort_prop'] == 'mchGoods.sort') {
                $query->leftJoin(['mg' => MchGoods::tableName()], 'mg.goods_id=g.id');
                $query->orderBy(['mg.sort' => $sortType]);
            } else {
                $query->orderBy(['g.' . $search['sort_prop'] => $sortType]);
            }
        } else {
            $query->orderBy(['g.created_at' => SORT_DESC]);
        }
        if (isset($search['status']) && $search['status'] != -1) {
            if ($search['status'] != '' && ($search['status'] == 0 || $search['status'] == 1)) {
                // 上下架状态
                $query->andWhere(['g.status' => $search['status']]);
            } elseif ($search['status'] == 2) {
                // 售罄
                $query->andWhere(['g.goods_stock' => 0]);
            }
        }

        // 分类搜索
        if (isset($search['cats']) && $search['cats']) {
            $query = $this->addCatWhere($search['cats'], $query);
        }

        // 日期搜索
        if (isset($search['date_start']) && $search['date_start'] && isset($search['date_end']) && $search['date_end']) {
            $query->andWhere(['>=', 'g.created_at', strtotime($search['date_start'])]);
            $query->andWhere(['<=', 'g.created_at', strtotime($search['date_end'])]);
        }

        $query = $this->setQuery($query);
        if (\Yii::$app->request->post('flag') == 'EXPORT') {
            return $query;
        }
        $newQuery   = clone $query;
        $goodsCount = $newQuery->count();
        /**
         * @var BasePagination $pagination
         */
        $list    = $query->with('goodsWarehouse.cats', 'attr')->page($pagination)->all();
        $newList = $this->handleData($list);

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg'  => '请求成功',
            'data' => [
                'list'        => $newList,
                'pagination'  => $pagination,
                'goods_count' => $goodsCount
            ]
        ];
    }

    function handleGoodsData($goods)
    {
        $newItem              = [];
        $newItem['mchGoods']  = isset($goods->mchGoods) ? ArrayHelper::toArray($goods->mchGoods) : [];
        $newItem['mchCats']   = isset($goods->goodsWarehouse->mchCats) ? ArrayHelper::toArray($goods->goodsWarehouse->mchCats) : [];
        $newItem['mallGoods'] = isset($goods->mallGoods) ? ArrayHelper::toArray($goods->mallGoods) : [];
        return $newItem;
    }
}