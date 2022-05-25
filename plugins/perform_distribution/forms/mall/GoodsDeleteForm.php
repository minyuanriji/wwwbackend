<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionGoods;

class GoodsDeleteForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required'],
            [['id'], 'integer']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {
            $goods = PerformDistributionGoods::findOne($this->id);
            if(!$goods){
                throw new \Exception("数据异常，商品信息不存在");
            }
            $goods->is_delete  = 1;
            if(!$goods->save()){
                throw new \Exception($this->responseErrorMsg($goods));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}