<?php

namespace app\plugins\smart_shop\forms\api\store_admin;

use app\core\ApiCode;
use app\models\BaseModel;

class StoreSetDetailForm extends BaseModel{

    public $merchant_id;
    public $store_id;

    public function rules(){
        return [
            [['merchant_id', 'store_id'], 'required']
        ];
    }

    public function get(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, null, [

            ]);
        }catch (\Exception $e){
            return $this->returnApiResultData(ApiCode::CODE_FAIL, $e->getMessage());
        }

    }

}