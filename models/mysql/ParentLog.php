<?php
namespace app\models\mysql;
class ParentLog extends Common{
    public function getParentLog($id){
        return $this -> find() -> where(['user_id' => $id]) -> one();
    }
}



