<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\OrderClerk;

class OrderClerkListForm extends BaseModel{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;
    public $order_type;
    public $express_status;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type', 'order_type', 'express_status'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = OrderClerk::find()->alias("oc")->with("orderDetail");

        $query->innerJoin("{{%order}} o", "o.id=oc.order_id");
        $query->innerJoin("{{%user}} u", "u.id=o.clerk_id");
        $query->innerJoin("{{%order_detail}} od", "od.order_id=oc.order_id");
        $query->innerJoin("{{%goods}} g", "g.id=od.goods_id");
        $query->innerJoin("{{%goods_warehouse}} gw", "g.goods_warehouse_id=gw.id");
        $query->leftJoin("{{%plugin_mch}} m", "m.user_id=u.id");
        $query->leftJoin("{{%store}} s", "s.mch_id=m.id");

        $query->where(["oc.is_delete" => 0]);

        if(!empty($this->order_type)){
            $query->andWhere(["o.order_type" => $this->order_type]);
        }

        if(!empty($this->express_status)){
            if($this->express_status == "no_express"){
                $query->andWhere(["oc.express_status" => 0]);
            }else{
                $query->andWhere(["oc.express_status" => 1]);
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
            $orderBy = "oc.id " . (!$this->sort_type   ? "DESC" : "ASC");
            if($this->sort_prop == "id"){
                $orderBy = "oc.id " . (!$this->sort_type ? "DESC" : "ASC");
            }elseif($this->sort_prop == "created_at"){
                $orderBy = "oc.created_at " . (!$this->sort_type ? "DESC" : "ASC");
            }
        }

        $query->orderBy($orderBy);

        $select = ["oc.id", "oc.express_status", "oc.order_id", "o.order_no", "o.order_type", "oc.clerk_remark", "u.nickname", "s.name as store_name", "o.created_at"];

        $list = $query->select($select)->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();
        if($list){
            foreach($list as &$item){
                foreach($item['orderDetail'] as $key => $detail){
                    $goodsInfo = !empty($detail['goods_info']) ? @json_decode($detail['goods_info'], true) : [];
                    $item['orderDetail'][$key]['goods_info'] = $goodsInfo;
                    $item['clerk_role'] = 'mall';
                    if(!empty($item['store_name'])){
                        $item['clerk_role'] = 'store';
                    }
                    $item['nickname'] = !empty($item['store_name']) ? $item['store_name'] : $item['nickname'];
                }
                $item['express_status'] = $item['express_status'] ? true : false;
            }
        }

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list' => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }

}