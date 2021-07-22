<?php

namespace app\plugins\giftpacks\forms\api;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\giftpacks\models\Giftpacks;

class HotelGiftpacksListForm extends BaseModel{

    public $page;
    public $lng;
    public $lat;

    public function getList(){

        if(!$this->validate()){
            throw new \Exception($this->responseErrorMsg());
        }

        try {

            Giftpacks::find();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => [

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