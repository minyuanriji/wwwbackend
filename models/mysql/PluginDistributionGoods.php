<?php
namespace app\models\mysql;
class PluginDistributionGoods extends Common{
    public function getDistributionData($id){
        return $this -> find() -> where(['goods_id' => $id]) -> asArray() -> one();
    }
}
