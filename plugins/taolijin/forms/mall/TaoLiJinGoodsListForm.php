<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;

class TaoLiJinGoodsListForm extends BaseModel{

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

        try {

            $query = TaolijinGoods::find()->where(["is_delete" => 0]);
            if (!empty($this->keyword)) {
                $query->andWhere([
                    'or',
                    ["id" => (int)$this->keyword],
                    ['LIKE', 'name', $this->keyword]
                ]);
            }

            $orderBy = null;
            if(!empty($this->sort_prop)){
                $this->sort_type = (int)$this->sort_type;
                if($this->sort_prop == "id"){
                    $orderBy = "id " . (!$this->sort_type ? "DESC" : "ASC");
                }
            }

            if(empty($orderBy)){
                $orderBy = "id " . (!$this->sort_type   ? "DESC" : "ASC");
            }

            $query->orderBy($orderBy);

            $selects = ["id", "deduct_integral", "price", "status", "name", "cover_pic", "pic_url", "video_url", "ali_url",
                "unit", "updated_at", "created_at", "gift_price", "ali_type", "ali_rate", "ali_unique_id", "ali_other_data"
            ];

            $list = $query->select($selects)->asArray()->page($pagination)->all();
            if($list){
                $aliTexts = ["jd" => "京东", "ali" => "阿里巴巴"];
                foreach($list as &$item){
                    $item['ali_text']       = isset($aliTexts[$item['ali_type']]) ? $aliTexts[$item['ali_type']] : "";
                    $item['pic_url']        = !empty($item['pic_url']) ? (array)json_decode($item['pic_url'], true) : [];
                    $item['ali_other_data'] = !empty($item['ali_other_data']) ? (array)json_decode($item['ali_other_data'], true) : [];
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
                'msg'  => $e->getMessage(),
                'error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
        }
    }
}