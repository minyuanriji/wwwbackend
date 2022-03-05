<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\taobao\models\TaobaoGoods;

class TaobaoGoodsListForm extends BaseModel {

    public $page;
    public $limit = 12;

    public function rules() {
        return [
            [['page', 'limit'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = TaobaoGoods::find()->alias("tbg")
                ->innerJoin(["g" => Goods::tableName()], "g.id=tbg.goods_id")
                ->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");

            $query->orderBy("tbg.id DESC");

            $selects = ["tbg.id", "tbg.goods_id", "tbg.created_at", "gw.name", "gw.cover_pic"];

            $list = $query->select($selects)->asArray()->page($pagination)->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
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