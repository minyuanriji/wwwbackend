<?php

namespace app\plugins\alibaba\forms\api;

use app\core\ApiCode;

class AlibabaDistributionOrderPreviewForm extends AlibabaDistributionOrderForm {

    public function rules(){
        return array_merge(parent::rules(), [

        ]);
    }

    public function preview(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $orderData = $this->getData();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $orderData
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage(),
                'error' => []
            ];
        }
    }

}