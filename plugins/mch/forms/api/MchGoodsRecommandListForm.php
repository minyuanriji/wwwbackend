<?php

namespace app\plugins\mch\forms\api;

use app\core\ApiCode;
use app\forms\api\APICacheDataForm;
use app\forms\api\ICacheForm;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\baopin\models\BaopinGoods;
use app\plugins\baopin\models\BaopinMchGoods;

class MchGoodsRecommandListForm extends BaseModel implements ICacheForm {

    public $page;
    public $store_id;

    public function rules(){
        return [
            [['store_id'], 'required'],
            [['page'], 'integer']
        ];
    }

    /**
     * @return APICacheDataForm
     */
    public function getSourceDataForm(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $query = BaopinGoods::find()->alias('bg');
            $query->innerJoin(["g" => Goods::tableName()], "g.id=bg.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->innerJoin(["bmg" => BaopinMchGoods::tableName()], "bmg.goods_id=bg.goods_id AND bmg.store_id='".$this->store_id."' AND bmg.is_delete=0");
            $query->andWhere([
                "AND",
                ["g.is_delete" => 0],
                ["gw.is_delete" => 0]
            ]);
            $query->orderBy("bmg.id DESC");

            $select = ["bg.id", "bg.goods_id", "gw.name", "gw.cover_pic", "g.goods_stock", "g.virtual_sales",
                "bg.created_at", "bg.updated_at", "g.price", "gw.original_price", "bmg.id as mch_baopin_id"];

            $list = $query->select($select)->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();

            return new APICacheDataForm([
                "sourceData" => [
                    'code' => ApiCode::CODE_SUCCESS,
                    'data' => [
                        'list' => $list ? $list : [],
                        'pagination' => $pagination,
                    ]
                ]
            ]);
        }catch (\Exception $e){
            return ['code' => ApiCode::CODE_FAIL, 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return array
     */
    public function getCacheKey() {
        $keys = ['store_id' => $this->store_id, 'page' => max(1, intval($this->page))];
        return $keys;
    }
}