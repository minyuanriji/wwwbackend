<?php
namespace app\plugins\taolijin\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;

class TaolijinGoodsSearchForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = TaolijinGoods::find()->where(["is_delete" => 0, "status" => 1]);

            $selects = ["id", "deduct_integral", "price", "name", "cover_pic", "unit", "gift_price", "ali_type"];

            $list = $query->select($selects)->asArray()->page($pagination, 20, $this->page)->all();

            if($list){
                $aliTexts = ["jd" => "京东", "ali" => "阿里巴巴"];
                foreach($list as &$item){
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