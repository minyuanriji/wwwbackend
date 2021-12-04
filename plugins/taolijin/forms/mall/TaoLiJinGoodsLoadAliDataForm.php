<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinAli;

class TaoLiJinGoodsLoadAliDataForm extends BaseModel{

    public $keyword;

    public function rules(){
        return array_merge(parent::rules(), [
            [['keyword'], 'safe']
        ]);
    }

    public function getData(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $query = TaolijinAli::find()->orderBy("id DESC")->where(["is_delete" => 0]);
            $list = $query->asArray()->page($pagination, 20, 1)->all();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "list" => $list ? $list : []
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