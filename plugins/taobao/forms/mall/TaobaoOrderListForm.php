<?php

namespace app\plugins\taobao\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use app\plugins\taobao\models\TaobaoGoods;

class TaobaoOrderListForm extends BaseModel {

    public $page;
    public $limit = 12;

    public function rules() {
        return [
            [['page', 'limit'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = OrderDetail::find()->alias("od")
                ->innerJoin(["g" => Goods::tableName()], "g.id=od.goods_id")
                ->rightJoin(["tbg" => TaobaoGoods::tableName()], "tbg.goods_id=g.id")
                ->innerJoin(["o" => Order::tableName()], "o.id=od.order_id")
                ->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id")
                ->innerJoin(["u" => User::tableName()], "u.id=o.user_id");


            $query->where([
                "o.is_pay" => 1,
                "o.cancel_status" => 0,
                "o.is_delete" => 0,
                "o.is_recycle" => 0
            ]);

            $query->orderBy("od.id DESC");

            $selects = ["tbg.id", "tbg.goods_id", "tbg.created_at", "gw.name", "gw.cover_pic",
                "od.num", "od.total_price", "od.is_refund", "od.refund_status",
                "o.id as order_id", "o.order_no", "u.id as user_id", "u.nickname", "tbg.url"
            ];

            $list = $query->select($selects)->asArray()->page($pagination)->all();

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