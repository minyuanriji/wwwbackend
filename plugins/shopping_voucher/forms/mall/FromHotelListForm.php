<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;

class FromHotelListForm extends BaseModel {

    public function rules(){
        return [
            [[], 'integer']
        ];
    }

    public function getList(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $list = [];
            $pagination = null;

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'pagination' => $pagination
            ]);
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }

}