<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\models\Store;
use app\plugins\mch\models\Mch;
use app\plugins\sign_in\forms\BaseModel;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\models\Order;

class OrderListForm extends BaseModel{

    public $page;
    public $mch_id;

    public function rules(){
        return [
            [['page', 'mch_id'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $query = Order::find()->alias("o")
                ->innerJoin(["m" => Mch::tableName()], "m.id=o.bsh_mch_id")
                ->innerJoin(["s" => Store::tableName()], "s.mch_id=o.bsh_mch_id");

            $query->andWhere(["o.is_delete" => 0]);

            $query->orderBy("o.id DESC");

            $selects = ["o.*", "m.mobile", "s.name", "s.cover_url", "m.transfer_rate"];

            $list = $query->select($selects)->asArray()->page($pagination, 10, $this->page)->all();
            $shop = new SmartShop();
            if($list){
                foreach($list as &$item){
                    $item['detail']     = $shop->getOrderDetail($item['from_table_name'], $item['from_table_record_id']);
                    $item['split_data'] = !empty($item['split_data']) ? (array)@json_decode($item['split_data'], true) : [];
                    if(!isset($item['split_data']['receivers']) || empty($item['split_data']['receivers'])){
                        $item['split_data']['receivers'] = [];
                    }
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                ]
            ];

        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}