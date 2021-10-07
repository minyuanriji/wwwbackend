<?php

namespace app\plugins\shopping_voucher\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\shopping_voucher\models\ShoppingVoucherFromHotel;

class FromHotelCommonSaveForm extends BaseModel{

    public $is_open;
    public $give_value;
    public $start_at;

    public function rules(){
        return [
            [['is_open', 'give_value', 'start_at'], 'required']
        ];
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $fromHotel = ShoppingVoucherFromHotel::findOne(["hotel_id" => 0]);
            if(!$fromHotel){
                $fromHotel = new ShoppingVoucherFromHotel([
                    "mall_id"    => \Yii::$app->mall->id,
                    "hotel_id"   => 0,
                    "created_at" => time()
                ]);
            }

            $fromHotel->give_type = 1;
            $fromHotel->give_value = max(0, min(100, $this->give_value));
            $fromHotel->updated_at = time();
            $fromHotel->is_delete  = $this->is_open ? 0 : 1;
            $fromHotel->start_at   = max(time(), strtotime($this->start_at));
            if(!$fromHotel->save()){
                throw new \Exception($this->responseErrorMsg($fromHotel));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '保存成功',
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg' => $e->getMessage()
            ];
        }
    }

}