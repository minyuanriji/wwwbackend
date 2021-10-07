<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\forms\common\UserIntegralModifyForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class UserCenterCancelOrderForm extends BaseModel {

    public $hotel_order_id;

    public function rules(){
        return [
            [['hotel_order_id'], 'required']
        ];
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            //获取订单详情
            $hotelOrder = HotelOrder::findOne($this->hotel_order_id);
            if(!$hotelOrder){
                throw new \Exception("订单不存在");
            }

            $isCancelable = OrderHelper::isCancelable($hotelOrder['order_status'], $hotelOrder['pay_status'], $hotelOrder['created_at'], $hotelOrder['booking_start_date'], $hotelOrder['booking_days']);
            if(!$isCancelable){
                throw new \Exception("订单无法取消");
            }

            $hotelOrder->order_status = "cancel";
            $hotelOrder->updated_at = time();
            if(!$hotelOrder->save()){
                throw new \Exception($this->responseErrorMsg($hotelOrder));
            }

            $user = User::findOne($hotelOrder->user_id);

            //返还红包
            if($hotelOrder->integral_deduction_price){
                $modifyForm = new UserIntegralModifyForm([
                    "type"        => 1,
                    "integral"    => $hotelOrder->integral_deduction_price,
                    "desc"        => "订单取消退还红包",
                    "source_id"   => $hotelOrder->id,
                    "source_type" => "hotel_order_cancel"
                ]);
                $modifyForm->modify($user);
            }

            $t->commit();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => "操作成功"
            ];
        }catch (\Exception $e){
            $t->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}