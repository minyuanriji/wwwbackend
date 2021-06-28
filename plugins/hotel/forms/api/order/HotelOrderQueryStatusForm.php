<?php
namespace app\plugins\hotel\forms\api\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class HotelOrderQueryStatusForm extends BaseModel{

    public $order_no;

    public function rules(){
        return [
            [['order_no'], 'required']
        ];
    }

    public function get(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $hotelOrder = HotelOrder::findOne(["order_no" => $this->order_no]);
            if(!$hotelOrder){
                throw new \Exception("订单不存在");
            }

            $plateform = $hotelOrder->getPlateform();
            if(!$plateform){
                throw new \Exception("无法获取平台信息");
            }

            $res = OrderHelper::queryPlateformOrder($hotelOrder, $plateform);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'order_state' => $res['data']['order_state']
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