<?php
/**
  * @link:http://www.gdqijianshi.com/
 * copyright: Copyright (c) 2020 广东七件事集团
 * author: zal
 */

namespace app\forms\mall\goods;


use app\forms\mall\export\MallGoodsExport;
use app\models\BaseQuery\BaseActiveQuery;
use app\services\Plugins\PluginsService;
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
            'g.sign' => \Yii::$app->admin->identity->mch_id > 0 ? 'mch' : '',
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
            $exp = new MallGoodsExport();
            $res = $exp->export($new_query);
            return $res;
        }

        return $query;
    }
    
    function handleGoodsData($goods)
    {
        $newItem = [];
        $newItem['mchGoods'] = isset($goods->mchGoods) ? ArrayHelper::toArray($goods->mchGoods) : [];
        $newItem['mchCats'] = isset($goods->goodsWarehouse->mchCats) ? ArrayHelper::toArray($goods->goodsWarehouse->mchCats) : [];
        $newItem['mallGoods'] = isset($goods->mallGoods) ? ArrayHelper::toArray($goods->mallGoods) : [];
        return $newItem;
    }
}