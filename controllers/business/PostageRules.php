<?php
namespace app\controllers\business;
use app\models\mysql\PostageRules as PostageRulesModel;
use app\models\mysql\Goods;
class PostageRules{
    /**
     * 获取快递价格
     * @param $data
     * @param int $flag
     * @return array
     */
    public function getExpressPrice($data,$flag = 0){
        try {
            if($flag == 0){
                $dataArr = [];
                foreach ($data['order_id'] as $key => $val){
                    $dataArr[] = [
                        'id' => $val['id'],
                        'num' => $val['num']
                    ];
                }
            }else{
                $dataArr = $data['order_id'];
            }

            $data_price = [];
            foreach ($dataArr as $key => $val) {

                $freight = (new Goods())->getGoodsFreightId($val['id']);
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

                $data_price[$val['id']] = $price * $val['num'];
            }
        }catch (\Exception $e){
            $data_price[0] = $e -> getMessage();
        }
        return $data_price;
    }

    /**
     * 获取快递信息
     * @param $id
     * @return mixed|null
     */
    public function getExpressData($id){
        if ($id == 0) {
            $result = (new PostageRulesModel())->getExpressPrice();
        } else {
            $result = (new PostageRulesModel())->getGoodsExpressPrice($id);
        }
        $data = json_decode($result -> toArray()['detail'],true);
        $data_arr = [];
        foreach ($data as $key => $val){
            $data_arr[] = $val['list'];
        }
        $result = array_reduce($data_arr, 'array_merge', []);
        return $result;
    }

    public function CustomPostage($data){
        $data_arr = [];
        foreach ($data as $key => $val){
            $postage = explode(',',$val);
            $data_arr[] = [
                'name' => $postage[0],
                'price' => $postage[1],
            ];
        }
        var_dump(json_encode($data_arr));
    }



}