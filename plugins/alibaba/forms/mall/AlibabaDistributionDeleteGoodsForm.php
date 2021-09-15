<?php

namespace app\plugins\alibaba\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\alibaba\models\AlibabaDistributionGoodsList;

class AlibabaDistributionDeleteGoodsForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function delete(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            AlibabaDistributionGoodsList::updateAll(["is_delete" => 1], "id IN(".implode(",", $this->id).")");

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