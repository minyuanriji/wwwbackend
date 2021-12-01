<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
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

            $acc = AliAccForm::get("ali");

            $ali = new Ali($acc->app_key, $acc->secret_key);
            $res = $ali->material->optimusSearch([
                "page_size"   => "12",
                "page_no"     => (string)$this->page,
                "adzone_id"   => $acc->adzone_id,
                "material_id" => "13366",
                "item_id"     => "588875086307"
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