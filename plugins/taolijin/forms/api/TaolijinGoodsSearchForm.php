<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;
use app\plugins\taolijin\models\TaolijinGoodsCatRelation;

class TaolijinGoodsSearchForm extends BaseModel{

    public $page;
    public $cat_id;

    public function rules(){
        return [
            [['cat_id'], 'required'],
            [['page', 'cat_id'], 'integer']
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = TaolijinGoods::find()->alias("g")->where(["g.is_delete" => 0, "g.status" => 1]);
            if($this->cat_id){
                $query->innerJoin(["gr" => TaolijinGoodsCatRelation::tableName()], "gr.goods_id=g.id");
                $query->andWhere([
                    "AND",
                    ["gr.cat_id" => $this->cat_id],
                    ["gr.is_delete" => 0]
                ]);
            }

            $selects = ["g.id", "g.deduct_integral", "g.price", "g.name", "g.cover_pic", "g.unit", "g.gift_price", "g.ali_type", "g.virtual_sales"];

            $list = $query->orderBy("g.id DESC")->select($selects)->asArray()->page($pagination, 20, $this->page)->all();

            if($list){
                $aliTexts = ["jd" => "京东", "ali" => "阿里巴巴"];
                foreach($list as &$item){
                    $item['sales'] = sprintf("已售%s%s", 0 + $item['virtual_sales'], $item['unit']);
                    unset($item['virtual_sales']);
                    $item['ali_text'] = isset($aliTexts[$item['ali_type']]) ? $aliTexts[$item['ali_type']] : "";
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '请求成功',
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