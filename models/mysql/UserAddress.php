<?php
namespace app\models\mysql;
class UserAddress extends Common{
    public function getUserAddress($user_id,$address_id){
        return $this -> find() -> where(['user_id' => $user_id,'id' => $address_id]) -> select('province,city,district,town,detail') -> asArray() -> one();
    }
}