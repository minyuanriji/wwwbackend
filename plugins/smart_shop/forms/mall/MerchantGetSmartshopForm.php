<?php

namespace app\plugins\smart_shop\forms\mall;

use app\core\ApiCode;
use app\plugins\sign_in\forms\BaseModel;

class MerchantGetSmartshopForm extends BaseModel{

    public function getList(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {



            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'pagination' => ['page_count' => 0]
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}