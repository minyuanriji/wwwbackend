<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\models\Goods;
use app\models\GoodsWarehouse;
use app\models\Order;
use app\models\OrderClerk;
use app\models\OrderClerkExpress;
use app\models\OrderClerkExpressDetail;
use app\models\OrderDetail;
use app\models\Store;
use app\models\User;
use app\plugins\baopin\models\BaopinMchGoods;
use app\plugins\mch\models\Mch;

class OrderClerkListForm extends BaseModel{

    public $store_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;
    public $order_type;
    public $express_status;

    public function rules(){
        return array_merge(parent::rules(), [
            [['store_id'], 'required'],
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type', 'order_type', 'express_status'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $store = Store::findOne($this->store_id);
            if(!$store){
                throw new \Exception("门店信息不存在");
            }

            $query = OrderDetail::find()->alias("od");
            $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
            $query->innerJoin(["oc" => OrderClerk::tableName()], "oc.order_id=o.id");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=od.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->innerJoin(["s" => Store::tableName()], "s.id=o.store_id");
            $query->innerJoin(["m" => Mch::tableName()], "m.id=s.mch_id");
            $query->innerJoin(["u" => User::tableName()], "u.id=m.user_id");

            $query->leftJoin(["bmg" => BaopinMchGoods::tableName()], "bmg.goods_id=g.id AND bmg.store_id='".$store->id."'");
            $query->leftJoin(["oce" => OrderClerkExpress::tableName()], "oce.order_detail_id=od.id");
            $query->leftJoin(["oced" => OrderClerkExpressDetail::tableName()], "oced.id=oce.express_detail_id");

            $query->where(["oc.is_delete" => 0]);
            $query->where(["s.id" => $this->store_id]);

            if(!empty($this->order_type)){
                $query->andWhere(["o.order_type" => $this->order_type]);
            }

            if(!empty($this->express_status)){
                if($this->express_status == "no_express"){
                    $query->andWhere("oce.id IS NULL");
                }else{
                    $query->andWhere("oce.id IS NOT NULL");
                }
            }

            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ['o.id' => (int)$this->keyword],
                    ['LIKE', 'u.nickname', $this->keyword],
                    ['LIKE', 'o.order_no', $this->keyword],
                    ['LIKE', 'gw.name', $this->keyword],
                    ['LIKE', 's.name', $this->keyword]
                ]);
            }

            $orderBy = null;
            if(!empty($this->sort_prop)){
                $this->sort_type = (int)$this->sort_type;
            }

            if(empty($orderBy)){
                $orderBy = "od.id " . (!$this->sort_type   ? "DESC" : "ASC");
                if($this->sort_prop == "id"){
                    $orderBy = "od.id " . (!$this->sort_type ? "DESC" : "ASC");
                }elseif($this->sort_prop == "created_at"){
                    $orderBy = "od.created_at " . (!$this->sort_type ? "DESC" : "ASC");
                }
            }

            $query->orderBy($orderBy);

            $select = ["od.id",  "od.order_id", "o.order_no", "o.order_type", "oc.clerk_remark", "u.nickname", "s.name as store_name", "oc.created_at",
                "oce.id as clerk_express_id", "oced.send_type", "oced.express_no", "oced.express_content", "oced.express", "oced.express_code",
                "od.goods_info", "od.num", "bmg.stock_num", "bmg.total_stock"
            ];

            $list = $query->select($select)->asArray()->page($pagination, 20, max(1, (int)$this->page))->all();
            if($list){
                foreach($list as &$item){
                    $item['stock_num'] = $item['stock_num'] ? (int)$item['stock_num'] : 0;
                    $item['total_stock'] = $item['total_stock'] ? (int)$item['total_stock'] : 0;
                    $item['goods_info'] = json_decode($item['goods_info'], true);
                    $item['express_status'] = $item['clerk_express_id'] ? true : false;
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list' => $list ? $list : [],
                    'store' => ArrayHelper::toArray($store),
                    'pagination' => $pagination,
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => $e->getMessage()
            ];
        }
    }

}