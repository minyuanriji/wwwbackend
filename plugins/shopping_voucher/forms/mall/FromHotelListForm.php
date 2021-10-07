<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromHotel;

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

            $commonData = ["is_open" => 0, "give_value"  => "", "start_at" => ""];
            $fromHotel = ShoppingVoucherFromHotel::findOne(["hotel_id" => 0]);
            if($fromHotel){
                $commonData["is_open"]    = !$fromHotel->is_delete ? 1 : 0;
                $commonData["give_value"] = $fromHotel->give_value;
                $commonData["start_at"]   = date("Y-m-d", $fromHotel->start_at);
            }

            $list = [];
            $pagination = null;

            return $this->returnApiResultData(ApiCode::CODE_SUCCESS, '', [
                'list'       => $list ? $list : [],
                'commonData' => $commonData,
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