<?php
namespace app\controllers\business;
use app\models\mysql\PostageRules as PostageRulesModel;
use app\models\mysql\Goods;
class PostageRules{
    public function getExpressPrice($data){
        try {
            $freight = (new Goods()) -> getGoodsFreightId($data['order_id']);
            if($freight['mch_id'] > 0){
                return ['price' => 0];
            }
            if($freight['freight_id'] == 0){
                $result = (new PostageRulesModel()) -> getExpressPrice();
            }else{
                $result = (new PostageRulesModel()) -> getGoodsExpressPrice($freight['freight_id']);
            }
            $result = json_decode($result -> toArray()['detail'],true);
            if(array_search($data['data'],array_column($result[0]['list'],'name')) !== false){
                $price = $result[0]['firstPrice'];
            }else{
                $price = $result[1]['firstPrice'];
            }
        }catch (\Exception $e){
            $price = [];
        }
        return ['price' => $price];
    }
}