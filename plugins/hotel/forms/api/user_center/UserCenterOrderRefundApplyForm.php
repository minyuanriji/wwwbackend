<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class UserCenterOrderRefundApplyForm extends BaseModel {

    public $hotel_order_id;

    public function rules(){
        return [
            [['hotel_order_id'], 'required']
        ];
    }

    public function apply(){
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
            if(!$isRefundable){
                throw new \Exception("订单无法退款");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}