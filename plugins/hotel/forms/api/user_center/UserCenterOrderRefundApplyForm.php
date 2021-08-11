<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\forms\common\HotelOrderRefundActionForm;
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
            if(!$hotelOrder || $hotelOrder->user_id != \Yii::$app->user->id){
                throw new \Exception("订单不存在");
            }

            $isRefundable = OrderHelper::isRefundable($hotelOrder);
            if(!$isRefundable){
                throw new \Exception("订单无法退款");
            }

            $plateform = $hotelOrder->getPlateform();
            if(!$plateform){
                throw new \Exception("无法获取平台信息");
            }

            //订单状态：0待确认 1预订成功 2已取消 3预订未到 4已入住 5已完成 6确认失败
            $res = OrderHelper::queryPlateformOrder($hotelOrder, $plateform);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            $orderState = $res['data']['order_state'];
            if(in_array($orderState, [0, 1, 6])){
                $res = OrderHelper::plateformOrderRefundApply($hotelOrder, $plateform);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }elseif(in_array($orderState, [3, 4, 5])){
                throw new \Exception("预订未到、已入住、已完成等状态无法取消");
            }

            //开始退款+退还红包、余额
            $refundForm = new HotelOrderRefundActionForm([
                "order_id" => $hotelOrder->id,
                "mall_id"  => \Yii::$app->mall->id,
                "action"   => "paid"
            ]);
            $res = $refundForm->refund($hotelOrder);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '退款成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}