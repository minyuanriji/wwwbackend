<?php

namespace app\plugins\perform_distribution\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\perform_distribution\models\PerformDistributionRegion;

class RegionDetailForm extends BaseModel{

    public $id;

    public function rules(){
        return [
            [['id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            $region = PerformDistributionRegion::findOne($this->id);
            if(!$region || $region->is_delete){
                throw new \Exception("数据异常！区域信息不存在");
            }

            $detail = $region->getAttributes();

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [
                'detail' => $detail
            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }
    }

}