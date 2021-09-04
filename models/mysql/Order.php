<?php
namespace app\models\mysql;
class Order extends Common{
    public function getOneOrderData($id){
        return $this -> find() -> where(['id' => $id]) -> select('id,total_price,use_score,integral_deduction_price,shopping_voucher_use_num') -> asArray() -> one();
    }
}

