<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use lin010\taolijin\Ali;

class TaoLiJinAliSearchForm extends BaseModel {

    public $page;

    public function rules(){
        return [
            [['page'], 'integer']
        ];
    }

    public function search(){


        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $appKey = "33062416";
            $secretKey = "a5de1941f9aa0c70101d110128ce0729";
            $ali = new Ali($appKey, $secretKey);
            $res = $ali->material->optimusSearch([
                "page_size"   => "12",
                "page_no"     => (string)$this->page,
                "adzone_id"   => "111611450447",
                "material_id" => "13366"
            ]);

            if(!empty($res->code)){
                throw new \Exception($res->msg);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $res->getMapData()
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