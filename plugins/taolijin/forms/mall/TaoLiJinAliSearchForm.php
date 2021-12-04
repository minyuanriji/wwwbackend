<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\forms\common\AliAccForm;
use app\plugins\taolijin\models\TaolijinAli;
use lin010\taolijin\Ali;

class TaoLiJinAliSearchForm extends BaseModel {

    public $ali_id;
    public $page;

    public function rules(){
        return [
            [['ali_id'], 'required'],
            [['page'], 'integer']
        ];
    }

    public function search(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $aliModel = TaolijinAli::findOne($this->ali_id);
            if(!$aliModel || $aliModel->is_delete){
                throw new \Exception("联盟数据不存在");
            }


            $acc = AliAccForm::getByModel($aliModel);

            $ali = new Ali($acc->app_key, $acc->secret_key);
            $res = $ali->material->search([
                "page_size"   => "12",
                "page_no"     => (string)$this->page,
                "adzone_id"   => $acc->adzone_id,
                "material_id" => "6268"
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