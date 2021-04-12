<?php
namespace app\mch\forms\baopin;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\baopin\models\BaopinMchGoods;

class GoodsListForm extends BaseModel{

    public $mch_id;
    public $page;
    public $keyword;
    public $sort_prop;
    public $sort_type;

    public function rules(){
        return array_merge(parent::rules(), [
            [['mch_id'], 'required'],
            [['page', 'mch_id'], 'integer'],
            [['keyword', 'sort_prop', 'sort_type'], 'safe']
        ]);
    }

    public function getList(){
        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        $pagination = null;
        $query = BaopinMchGoods::find()->alias('bcg')
                    ->innerJoin("{{%plugin_baopin_goods}} bg", "bg.goods_id=bcg.goods_id")
                    ->innerJoin("{{%goods}} g", "g.id=bg.goods_id")
                    ->innerJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->andWhere(["bcg.mch_id" => $this->mch_id]);
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
            }elseif($this->sort_prop == "goods_name"){
                $orderBy = "gw.name " . (!$this->sort_type? "DESC" : "ASC");
            }elseif($this->sort_prop == "sort"){
                $orderBy = "bcg.sort " . (!$this->sort_type? "DESC" : "ASC");
            }
        }

        if(empty($orderBy)){
            $orderBy = "bg.id " . (!$this->sort_type   ? "DESC" : "ASC");
        }

        $query->orderBy($orderBy);

        $select = ["bg.id", "bcg.sort", "bg.goods_id", "gw.name", "gw.cover_pic", "bg.created_at",
            "g.enable_score", "bg.updated_at", "g.score_setting", "g.enable_integral", "g.integral_setting"];
        $list = $query->select($select)->asArray()->page($pagination)->all();
        if($list){
            foreach($list as $key => $row){

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