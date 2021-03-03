<?php
namespace app\models\mysql;
class UserParent extends Common{
    public function getUserParentData($user_id){
        return $this -> find() -> where(['user_id' => $user_id,'level' => 1]) -> asArray() -> one();
    }
}




