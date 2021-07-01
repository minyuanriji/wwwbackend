<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class UserCenterOrderRefundableForm extends BaseModel {

    public $hotel_order_id;

    public function rules(){
        return [
            [['hotel_order_id'], 'required']
        ];
    }

    public function get(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            //获取订单详情
            $hotelOrder = HotelOrder::findOne($this->hotel_order_id);
            if(!$hotelOrder){
                throw new \Exception("订单不存在");
            }

            $isRefundable = OrderHelper::isRefundable($hotelOrder);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data'  => [
                    "is_refundable" => $isRefundable ? 1 : 0
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