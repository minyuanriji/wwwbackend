<?php
namespace app\plugins\baopin\forms\mall;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Goods;

class SearchGoodsForm extends BaseModel{

    public $page;
    public $keyword;

    public function rules(){
        return array_merge(parent::rules(), [
            [['page'], 'integer'],
            [['keyword'], 'safe']
        ]);
    }

    public function search(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $query = Goods::find()->alias("g");
        $query->leftJoin("{{%plugin_baopin_goods}} pbg", "pbg.goods_id=g.id");
        $query->leftJoin("{{%goods_warehouse}} gw", "gw.id=g.goods_warehouse_id");
        $query->andWhere([
            "AND",
            ["g.is_delete" => 0],
            "pbg.id IS NULL"
        ]);

        if(!empty($this->keyword)){
            $query->andWhere([
                "OR",
                ["LIKE", "gw.name", $this->keyword],
                ["g.id" => $this->keyword]
            ]);
        }

        $query->orderBy(['g.id' => SORT_DESC]);

        $select = ["g.id", "gw.name", "gw.cover_pic", "g.created_at",  "g.updated_at"];
        $list = $query->select($select)->asArray()->page($pagination, 10, $this->page)->all();

        return [
            'code' => ApiCode::CODE_SUCCESS,
            'msg' => '请求成功',
            'data' => [
                'list'       => $list ? $list : [],
                'pagination' => $pagination,
            ]
        ];
    }
}