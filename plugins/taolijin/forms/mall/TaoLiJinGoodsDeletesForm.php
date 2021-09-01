<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinGoods;

class TaoLiJinGoodsDeletesForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function recycle(){

        if(!$this->responseErrorInfo()){
            return $this->responseErrorInfo();
        }

        try {

            $idArray = explode(",", $this->id);

            TaolijinGoods::updateAll([
                "is_delete"  => 1,
                "updated_at" => time()
            ], "id IN (".implode(",", $idArray).")");

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}