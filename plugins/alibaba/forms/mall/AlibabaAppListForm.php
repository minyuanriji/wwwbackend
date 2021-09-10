<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaApp;

class AlibabaAppListForm extends BaseModel{

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function getList(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = AlibabaApp::find()->where(["is_delete" => 0]);
            $orderBy = "id DESC";

            $query->orderBy($orderBy);

            $list = $query->asArray()->page($pagination, 20, $this->page)->all();
            if($list){
                foreach($list as &$item){
                    $item['token_info'] = !empty($item['token_info']) ? @json_decode($item['token_info'], true) : [];
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