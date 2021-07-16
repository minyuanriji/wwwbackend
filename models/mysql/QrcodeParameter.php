<?php
namespace app\models\mysql;
class QrcodeParameter extends Common{
    public function getParentData($source){
        return $this -> find() -> where(['token' => $source]) -> select('data') -> asArray() -> one();
    }
}

