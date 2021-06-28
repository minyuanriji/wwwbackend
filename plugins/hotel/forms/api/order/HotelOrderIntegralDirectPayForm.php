<?php
namespace app\plugins\hotel\forms\api\order;


use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class HotelOrderIntegralDirectPayForm extends BaseModel{

    public $order_no;

    public function rules(){
        return [
            [['order_no'], 'required']
        ];
    }

    public function pay(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->db->beginTransaction();
        try {

            $hotelOrder = HotelOrder::findOne(["order_no" => $this->order_no]);
            if(!$hotelOrder){
                throw new \Exception("订单不存在");
            }

            if(!OrderHelper::isPayable($hotelOrder)){
                throw new \Exception("订单不可支付");
            }

            //用红包抵扣需要的数量
            $integralPrice = OrderHelper::getIntegralPrice($hotelOrder->order_price);

            //用户
            $user = User::findOne($hotelOrder->user_id);
            if(!$user || $user->is_delete){
                throw new \Exception("无法获取用户信息");
            }

            if($user->static_integral < $integralPrice){
                throw new \Exception("红包数量不足");
            }

            //扣取红包
            $res = UserIntegralForm::hotelOrderPaySub($hotelOrder, $user, $integralPrice);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception("红包扣取失败：" . $res['msg']);
            }

            //平台下单
            $plateform = $hotelOrder->getPlateform();
            if(!$plateform){
                throw new \Exception("无法获取平台信息");
            }

            //第一次调用第三方平台下单接口
            if($plateform->source_code == $plateform->plateform_code){
                $res = OrderHelper::submitPlateformOrder($hotelOrder, $plateform);
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
                $plateform->plateform_code = $res['data']['plateform_order_no'];
                if(!$plateform->save()){
                    throw new \Exception($this->responseErrorMsg($plateform));
                }
            }

            //更新订单状态为已支付
            $hotelOrder->order_status             = "unconfirmed";
            $hotelOrder->pay_status               = "paid";
            $hotelOrder->pay_at                   = date("Y-m-d H:i:s");
            $hotelOrder->integral_deduction_price = $integralPrice;
            if(!$hotelOrder->save()){
                throw new \Exception($this->responseErrorMsg($hotelOrder));
            }

            $trans->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '支付成功'
            ];
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}