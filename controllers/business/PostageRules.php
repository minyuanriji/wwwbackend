<?php
namespace app\controllers\business;
use app\models\mysql\PostageRules as PostageRulesModel;
class PostageRules{
    public function getExpressPrice($data){
        try {
            $result = (new PostageRulesModel()) -> getExpressPrice();
            $result = json_decode($result -> toArray()['detail'],true);
            if(array_search($data['data'],array_column($result[0]['list'],'name')) !== false){
                $price = $result[0]['firstPrice'];
            }else{
                $price = $result[1]['firstPrice'];
            }
        }catch (\Exception $e){
            $price = [];
        }
        return $price;
    }
}