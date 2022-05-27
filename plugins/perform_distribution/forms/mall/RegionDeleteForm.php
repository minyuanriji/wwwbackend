<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionRegion;

class RegionDeleteForm extends BaseModel{

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
            $region = PerformDistributionRegion::findOne($this->id);
            if(!$region){
                throw new \Exception("数据异常，区域信息不存在");
            }
            $region->is_delete  = 1;
            if(!$region->save()){
                throw new \Exception($this->responseErrorMsg($region));
            }
            return $this->returnApiResultData(ApiCode::CODE_SUCCESS);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}