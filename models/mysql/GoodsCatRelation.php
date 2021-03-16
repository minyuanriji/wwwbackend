<?php
namespace app\models\mysql;
class GoodsCatRelation extends Common{
    public function getGoodsCatId($goods_warehouse_id){ 
        return $this -> find() -> where(['goods_warehouse_id' => $goods_warehouse_id,'is_delete' => 0]) -> asArray() -> all();
    }
}







