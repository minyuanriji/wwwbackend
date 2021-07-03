<?php
namespace app\models\mysql;
class MemberLevel extends Common{
    public function getOneLevelData(){
        return $this -> find() -> where(['id' => 4]) -> select('goods_warehouse_ids,upgrade_type_goods,goods_type,buy_compute_way') -> asArray() -> one();
    }
}

