<?php
namespace app\plugins\hotel\forms\common;


use app\core\ApiCode;
use app\forms\common\UserIntegralModifyForm;
use app\helpers\PaymentRefundHelper;
use app\models\BaseModel;
use app\models\PaymentOrder;
use app\models\User;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRefundApplyOrder;

class HotelOrderRefundActionForm extends BaseModel {

    public $order_id;
    public $mall_id;
    public $action;
    public $remark;

    public function rules(){
        return [
            [['order_id', 'mall_id', 'action'], 'required'],
            [['remark'], 'safe']
        ];
    }

    /**
     * 执行操作
     * @return array
     */
    public function refund($order = null){
        if($order && ($order instanceof HotelOrder)){
            $this->order_id = $order->id;
        }

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->db->beginTransaction();

        try {

            if(!$order){
                $order = HotelOrder::findOne($this->order_id);
            }

            if(!$order){
                throw new \Exception("订单不存在");
            }

            if(!in_array($this->action, ["apply", "confirm", "refuse", "paid"])){
                throw new \Exception("无效操作 " . $this->action);
            }

            //申请操作
            if($this->action == "apply"){
                if($order->pay_status != "paid"){
                    throw new \Exception("当前状态无法申请退款");
                }
                $order->pay_status = "refunding";
                $applyOrder = $this->apply();
            }else{
                $applyOrder = HotelRefundApplyOrder::findOne([
                    "order_id" => $this->order_id
                ]);
            }

            if(!$applyOrder){
                throw new \Exception("退款申请记录不存在");
            }

            $applyOrder->updated_at = time();
            if(!empty($this->remark)){
                $applyOrder->remark = $this->remark;
            }

            //确认操作
            if($this->action == "confirm"){
                $this->confirm($order, $applyOrder);
            }

            //拒绝操作
            if($this->action == "refuse"){
                $this->refuse($applyOrder, $order);
            }

            //打款操作
            if($this->action == "paid"){
                $this->paid($applyOrder, $order);
            }

            $order->updated_at = time();
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            if(!$applyOrder->save()){
                throw new \Exception($this->responseErrorMsg($applyOrder));
            }

            $trans->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '操作成功'
            ];

        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 申请操作
     * @return array
     */
    private function apply(){
        $applyOrder = HotelRefundApplyOrder::findOne([
            "order_id" => $this->order_id
        ]);
        if(!$applyOrder){
            $applyOrder = new HotelRefundApplyOrder([
                "mall_id"    => $this->mall_id,
                "order_id"   => $this->order_id,
                "status"     => "unconfirmed",
                "created_at" => time(),
                "updated_at" => time()
            ]);
            if(!$applyOrder->save()){
                throw new \Exception($this->responseErrorMsg($applyOrder));
            }
        }
        return $applyOrder;
    }

    /**
     * 确认操作
     * @return array
     */
    private function confirm(HotelOrder $hotelOrder, HotelRefundApplyOrder $applyOrder){
        if($this->action == "confirmed" && $applyOrder->status != "unconfirmed"){
            throw new \Exception("非申请状态无法执行确认操作");
        }
        $applyOrder->status = "confirmed";
        $plateform = $hotelOrder->getPlateform();
        if(!$plateform){
            throw new \Exception("无法获取平台信息");
        }

        //查询订单
        $res = OrderHelper::queryPlateformOrder($hotelOrder, $plateform);
        if($res['code'] != ApiCode::CODE_SUCCESS){
            throw new \Exception($res['msg']);
        }

        //是否可以退款
        //订单状态：0待确认 1预订成功 2已取消 3预订未到 4已入住 5已完成 6确认失败
        $orderState = $res['data']['order_state'];
        if(in_array($orderState, [0, 1, 6])){
            $res = OrderHelper::plateformOrderRefundApply($hotelOrder, $plateform);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
        }elseif(in_array($orderState, [3, 4, 5])){
            throw new \Exception("预订未到、已入住、已完成等状态无法取消");
        }
    }

    /**
     * 拒绝操作
     * @return array
     */
    private function refuse(HotelRefundApplyOrder $applyOrder, HotelOrder $order){
        if($this->action == "refused" && $applyOrder->status != "confirmed"){
            throw new \Exception("非确认状态无法执行拒绝操作");
        }
        $order->order_status = "refused";
        $applyOrder->status = "refused";
    }

    /**
     * 退款操作
     * @return array
     */
    private function paid(HotelRefundApplyOrder $applyOrder, HotelOrder $hotelOrder){
        if($this->action == "paid" && $applyOrder->status != "confirmed"){
            throw new \Exception("非确认状态无法执行打款操作");
        }

        $user = User::findOne($hotelOrder->user_id);
        if(!$user || $user->is_delete){
            throw new \Exception("用户[ID:".$hotelOrder->user_id."]不存在");
        }

        $applyOrder->status = "paid";
        $hotelOrder->pay_status = "refund"; //设置订单为已退款

        if($hotelOrder->integral_deduction_price > 0){ //退金豆
            $modifyForm = new UserIntegralModifyForm([
                "type"        => 1,
                "integral"    => $hotelOrder->integral_deduction_price,
                "desc"        => "酒店预订单退款，返还金豆",
                "source_id"   => $applyOrder->id,
                "source_type" => "hotel_order_refund"
            ]);
            $modifyForm->modify($user);
            $applyOrder->refund_integral = $hotelOrder->integral_deduction_price;
        }elseif($hotelOrder->pay_type == "balance"){ //退余额
            $applyOrder->refund_balance = $hotelOrder->pay_price;
        }else{ //退现金
            $applyOrder->refund_price = $hotelOrder->pay_price;
        }

        $payment = PaymentOrder::findOne(["order_no" => $hotelOrder->order_no]);
        $payment && PaymentRefundHelper::doRefund($user, $payment, "hotel_order_refund", $applyOrder->id, "酒店预订单退款");
    }


}