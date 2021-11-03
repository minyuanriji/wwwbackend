<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;

class OilSubmitPreviewForm extends OilBaseSubmitForm {

    public function preview(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $data = $this->buildOrderData();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => $data['order_data']
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }


}