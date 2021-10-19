<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromGoods;

class FromGoodsSearchGoodsForm extends BaseModel {

    public $id;
    public $name;
    public $page;

    public function rules(){
        return [
            [['id', 'page'], 'integer'],
            [['name'], 'safe']
        ];
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $query = Goods::find()->alias("g");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->leftJoin(["svfg" => ShoppingVoucherFromGoods::tableName()], "g.id=svfg.goods_id");
            $query->orderBy("g.id DESC");
            $query->where([
                "g.is_delete" => 0
            ]);

            //指定商品ID
            if($this->id){
                $query->andWhere(["g.id" => $this->id]);
            }

            //按名称模糊搜索
            if($this->name){
                $query->andWhere(["LIKE", "gw.name", $this->name]);
            }

            $selects = ["g.id", "g.id as goods_id", "g.mall_id",  "gw.name", "gw.cover_pic",  "g.created_at"];
            $selects = array_merge($selects, ["svfg.give_value", "svfg.give_type"]);

            $query->select($selects);

            $list = $query->page($pagination, 10, $this->page)->asArray()->all();
            if($list){
                foreach($list as &$item){
                    $item['created_at'] = date("Y-m-d", $item['created_at']);
                }
            }

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}