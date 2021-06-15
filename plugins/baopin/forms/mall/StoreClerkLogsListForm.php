<?php
namespace app\plugins\baopin\forms\mall;


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

class StoreClerkLogsListForm extends BaseModel{

    public $store_id;
    public $goods_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['store_id', 'goods_id'], 'required'],
            [['page', 'store_id'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $store = Store::findOne($this->store_id);
            if(!$store){
                throw new \Exception("门店不存在");
            }

            $goods = Goods::findOne($this->goods_id);
            if(!$goods){
                throw new \Exception("商品不存在");
            }

            $query = OrderDetail::find()->alias("od");
            $query->innerJoin(["o" => Order::tableName()], "o.id=od.order_id");
            $query->innerJoin(["oc" => OrderClerk::tableName()], "oc.order_id=o.id");
            $query->innerJoin(["g" => Goods::tableName()], "g.id=od.goods_id");
            $query->innerJoin(["gw" => GoodsWarehouse::tableName()], "gw.id=g.goods_warehouse_id");
            $query->leftJoin(["oce" => OrderClerkExpress::tableName()], "oce.order_detail_id=od.id");
            $query->leftJoin(["oced" => OrderClerkExpressDetail::tableName()], "oced.id=oce.express_detail_id");

            $query->andWhere([
                "o.store_id"  => $this->store_id,
                "od.goods_id" => $this->goods_id,
                "o.is_delete" => 0
            ]);

            if(!empty($this->keyword)){
                $query->andWhere([
                    "OR",
                    ["LIKE", "o.order_no", $this->keyword]
                ]);
            }

            $select = ["oc.id", "oc.express_status", "o.order_no", "gw.name", "gw.cover_pic", "oc.updated_at"];
            $select = array_merge($select, [
                "oced.send_type", "oced.express_content", "oced.express_no",
                "oced.express", "oced.express_code"
            ]);

            $query->select($select);

            $orderBy = null;
            if(!empty($this->sort_prop)){
                $this->sort_type = (int)$this->sort_type;
                if($this->sort_prop == "id"){
                    $orderBy = "oc.id " . (!$this->sort_type ? "DESC" : "ASC");
                }
            }else{
                $orderBy = "oc.id DESC" ;
            }

            $query->orderBy($orderBy);

            $list = $query->select($select)->asArray()->page($pagination)->all();
            if($list){
                foreach($list as &$item){
                    $item['updated_at'] = date("Y-m-d H:i", $item['updated_at']);
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
                'data' => [
                    'list'       => $list ? $list : [],
                    'pagination' => $pagination,
                    'store'      => ArrayHelper::toArray($store),
                    'goods_info'  => ArrayHelper::toArray($goods->goodsWarehouse)
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