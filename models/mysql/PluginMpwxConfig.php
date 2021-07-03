<?php
namespace app\models\mysql;
class PluginMpwxConfig extends Common{
    public function getConfig(){
        return $this -> find() -> where(['id' => 1]) -> select("app_id,secret") -> asArray() -> one();
    }
}