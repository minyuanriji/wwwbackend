<?php
namespace app\models\mysql;
class PostageRules extends Common {
    public function getExpressPrice(){
        return $this -> find() -> where(['status' => 1]) ->one();
    }
}