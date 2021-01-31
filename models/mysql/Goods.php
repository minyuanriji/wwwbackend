<?php
namespace app\models\mysql;
class Goods extends Common{
    public function getOneNavData($id){
        return $this -> find() -> select(['forehead_score','max_deduct_integral']) -> where(['id' => $id]) -> asArray() -> one();
    }
}