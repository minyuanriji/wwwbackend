<?php

namespace app\plugins\smart_shop\forms\mall;

use app\plugins\sign_in\forms\BaseModel;

class MerchantListForm extends BaseModel{

    public function getList(){

        if($this->validate()){
            return $this->responseErrorInfo();
        }

        try {

        }catch (\Exception $e){

        }
    }

}