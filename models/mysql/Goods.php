<?php
namespace app\models\mysql;
class Goods extends Common{
    public function getOneNavData($id){
        return $this -> find() -> select('forehead_score','max_deduct_integral') -> where(['id' => $id]) -> asArray() -> one();
    }

    public function getGoodsFreightId($id){
        return $this -> find() -> select('freight_id,mch_id') -> where(['id' => $id]) -> asArray() -> one();
    }

    public function getGoodsData($id){
        return $this -> find() -> where(['id' => $id]) -> asArray() -> one();
    }

}