<?php

namespace app\plugins\giftpacks\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Store;
use app\plugins\mch\models\Mch;

class GiftpacksGoodsListForm extends BaseModel{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList() {
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = Goods::find()->alias("g");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->innerJoin(["m" => Mch::tableName()], "m.id=g.mch_id");
            $query->innerJoin(["s" => Store::tableName()], "s.mch_id=m.id");
            $query->andWhere([
                "AND",
                ["g.is_delete" => 0],
                ["g.is_recycle" => 0],
                ["gw.is_delete" => 0]
            ]);
            $query->andWhere("g.mch_id > '0'");

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["LIKE", "gw.name", $this->keyword],
                    ["g.id" => $this->keyword],
                    ["LIKE", "s.name", $this->keyword],
                    ["m.id" => $this->keyword]
                ]);
            }

            $query->orderBy(['g.id' => SORT_DESC]);

            $select = ["g.id as goods_id", "gw.cover_pic", "g.price as goods_price", "gw.name as goods_name", "gw.cover_pic", "s.name as store_name", "s.id as store_id"];

            $list = $query->select($select)->asArray()->page($pagination, 10, $this->page)->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
                    'pagination' => $pagination
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