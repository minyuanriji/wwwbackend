<?php
namespace app\plugins\baopin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinGoods;

class SearchForm extends BaseModel{

    public $mch_id;
    public $filter_mch_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page', 'mch_id', 'filter_mch_id'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function search(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $pagination = null;
        $query = BaopinGoods::find()->alias('bg')
                    ->innerJoin("{{%goods}} g", "g.id=bg.goods_id")
                    ->innerJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");

        if(!empty($this->mch_id)){
            $query->innerJoin("{{%plugin_baopin_mch_goods}} bmg", "bmg.goods_id=bg.goods_id AND bmg.mch_id='".$this->mch_id."' AND bmg.is_delete=0");
        }

        if(!empty($this->filter_mch_id)){
            $query->leftJoin("{{%plugin_baopin_mch_goods}} bmg", "bmg.goods_id=bg.goods_id AND bmg.mch_id='".$this->filter_mch_id."' AND bmg.is_delete=0");
            $query->andWhere("bmg.id IS NULL");
        }

        $query->andWhere([
            "AND",
            ["g.is_delete" => 0],
            ["gw.is_delete" => 0]
        ]);

        if (!empty($this->keyword)) {
            $query->andWhere([
                'or',
                ['LIKE', 'g.id', $this->keyword],
                ['LIKE', 'gw.name', $this->keyword]
            ]);
        }

        $orderBy = null;
        if(!empty($this->sort_prop)){
            $this->sort_type = (int)$this->sort_type;
            if($this->sort_prop == "goods_id"){
                $orderBy = "bg.goods_id " . (!$this->sort_type ? "DESC" : "ASC");
            }elseif($this->sort_prop == "virtual_sales"){
                $orderBy = "g.virtual_sales " . (!$this->sort_type? "DESC" : "ASC");
            }elseif($this->sort_prop == "goods_stock"){
                $orderBy = "g.goods_stock " . (!$this->sort_type? "DESC" : "ASC");
            }
        }

        if(empty($orderBy)){
            $orderBy = "bg.id " . (!$this->sort_type   ? "DESC" : "ASC");
        }

        $query->orderBy($orderBy);

        $select = ["bg.id", "bmg.id as mch_baopin_id", "bg.goods_id", "gw.name", "gw.cover_pic",
            "g.goods_stock", "g.virtual_sales", "bg.created_at", "bg.updated_at",
            "g.price", "gw.original_price"];
        $list = $query->select($select)->asArray()->page($pagination, 10, max(1, (int)$this->page))->all();

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