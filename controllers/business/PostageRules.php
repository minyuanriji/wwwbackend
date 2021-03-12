<?php
namespace app\controllers\business;
use app\models\mysql\PostageRules as PostageRulesModel;
use app\models\mysql\Goods;
class PostageRules{
    public function getExpressPrice($data){
        try {
            $dataArr = explode(',', $data['order_id']);
            $dataArr = array_filter($dataArr);
            $data_price = [];
            foreach ($dataArr as $key => $val) {
                $freight = (new Goods())->getGoodsFreightId($val);

                if ($freight['mch_id'] > 0) {
                    $price = 0;
                } else {
                    if ($freight['freight_id'] == 0) {
                        $result = (new PostageRulesModel())->getExpressPrice();
                    } else {
                        $result = (new PostageRulesModel())->getGoodsExpressPrice($freight['freight_id']);
                    }
                    $result = json_decode($result->toArray()['detail'], true);
                    if (array_search($data['data'], array_column($result[0]['list'], 'name')) !== false) {
                        $price = $result[0]['firstPrice'];
                    } else {
                        $price = $result[1]['firstPrice'];
                    }
                }
                $data_price[$val] = $price;
            }
        }catch (\Exception $e){
            $data_price[0] = $e -> getMessage();
        }
        return $data_price;
    }
}