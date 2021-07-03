<?php


namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchClerkOrder;

class ClerkListForm extends BaseModel{

    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $query = BaopinMchClerkOrder::find()->alias("mco");
        $query->innerJoin("{{%plugin_mch}} m", "m.id=mco.mch_id");
        $query->innerJoin("{{%store}} s", "s.id=mco.store_id");
        $query->innerJoin("{{%plugin_baopin_mch_goods}} bmg", "bmg.goods_id=mco.goods_id AND bmg.mch_id=mco.mch_id AND bmg.store_id=mco.store_id");
        $query->innerJoin("{{%order}} o", "o.id=mco.order_id");
        $query->innerJoin("{{%order_detail}} od", "od.order_id=mco.order_id");
        $query->leftJoin("{{%goods}} g", "g.id=mco.goods_id");
        $query->leftJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->where([
            "mco.is_delete" => 0
        ]);

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ['mco.goods_id' => (int)$this->keyword],
                ['mco.mch_id' => (int)$this->keyword],
                ['LIKE', 'gw.name', $this->keyword],
                ['LIKE', 'o.order_no', $this->keyword],
                ['LIKE', 's.name', $this->keyword]
            ]);
        }

        $orderBy = null;
        if(!empty($this->sort_prop)){
            $this->sort_type = (int)$this->sort_type;
        }

        if(empty($orderBy)){
            $orderBy = "mco.id " . (!$this->sort_type   ? "DESC" : "ASC");
            if($this->sort_prop == "goods_id"){
                $orderBy = "mco.goods_id " . (!$this->sort_type ? "DESC" : "ASC");
            }elseif($this->sort_prop == "created_at"){
                $orderBy = "mco.created_at " . (!$this->sort_type ? "DESC" : "ASC");
            }
        }

        $query->orderBy($orderBy);

        $select = ["mco.id", "mco.goods_id", "od.goods_info", "mco.created_at",
            "od.num", "s.name as store_name", "s.mch_id"];

        $list = $query->select($select)->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();

        if($list) {
            foreach ($list as &$row) {
                $goodsInfo = !empty($row['goods_info']) ? @json_decode($row['goods_info'], true) : [];
                $row['goods_attr'] = !empty($goodsInfo['goods_attr']) ? $goodsInfo['goods_attr'] : [];
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