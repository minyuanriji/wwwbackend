<?php
namespace app\mch\forms\goods;


use app\forms\mall\export\MallGoodsExport;
use app\forms\mall\goods\BaseGoodsList;
use app\models\BaseQuery\BaseActiveQuery;
use yii\helpers\ArrayHelper;

class GoodsListForm extends BaseGoodsList {

    public $choose_list;
    public $flag;
    /**
     * @param BaseActiveQuery $query
     * @return mixed
     */
    protected function setQuery($query){

        $mchAdmin = \Yii::$app->mchAdmin->identity;

        $query->andWhere([
            'g.sign'    => 'mch',
            'g.mch_id'  => $mchAdmin->mchModel->id,
        ])->with('mallGoods');

        $query->with('mchGoods', 'goodsWarehouse.mchCats');

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
    
    function handleGoodsData($goods){
        $newItem = [];
        $newItem['mchGoods']    = isset($goods->mchGoods) ? ArrayHelper::toArray($goods->mchGoods) : [];
        $newItem['mchCats']     = isset($goods->goodsWarehouse->mchCats) ? ArrayHelper::toArray($goods->goodsWarehouse->mchCats) : [];
        $newItem['mallGoods']   = isset($goods->mallGoods) ? ArrayHelper::toArray($goods->mallGoods) : [];
        return $newItem;
    }
}