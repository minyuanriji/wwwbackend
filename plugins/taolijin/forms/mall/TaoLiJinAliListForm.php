<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class TaoLiJinAliListForm extends BaseModel{

    public $keyword;
    public $ali_type;
    public $sort_prop;
    public $sort_type;
    public $page;

    public function rules(){
        return [
            [['page'], 'integer'],
            [['keyword', 'ali_type', 'sort_prop', 'sort_type'], 'safe']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = TaolijinAli::find()->where(["is_delete" => 0]);

            $orderBy = null;
            if(!empty($this->sort_prop)){
                $this->sort_type = (int)$this->sort_type;
            }

            if(empty($orderBy)){
                $orderBy = "id " . (!$this->sort_type   ? "DESC" : "ASC");
            }

            $query->orderBy($orderBy);

            $list = $query->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['is_open'] = (int)$item['is_open'];
                    $item['settings_data'] = !empty($item['settings_data']) ? json_decode($item['settings_data'], true) : [];
                }
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $list ? $list : [],
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