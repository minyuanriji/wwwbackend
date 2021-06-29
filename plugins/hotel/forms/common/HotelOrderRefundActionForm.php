<?php
namespace app\plugins\hotel\forms\common;


use app\core\ApiCode;
use app\forms\common\UserIntegralForm;
use app\models\BaseModel;
use app\models\User;
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
     * 打款操作
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

            $applyOrder = null;

            //申请操作
            $order->pay_status = "refunding";
            if(in_array($this->action, ["apply", "confirm", "refuse", "paid"])){
                $applyOrder = $this->apply();
            }

            if(!empty($this->remark)){
                $applyOrder->remark = $this->remark;
            }

            $applyOrder->updated_at = time();

            //确认操作
            if(in_array($this->action, ["confirm", "refuse", "paid"])){
                $this->confirm($applyOrder);

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
        $applyOrder = HotelRefundApplyOrder::findOne($this->order_id);
        if(!$applyOrder){
            $applyOrder = new HotelRefundApplyOrder([
                "mall_id"    => $this->mall_id,
                "order_id"   => $this->order_id,
                "status"     => "unconfirmed",
                "created_at" => time(),
                "updated_at" => time()
            ]);
        }
        return $applyOrder;
    }

    /**
     * 确认操作
     * @return array
     */
    private function confirm(HotelRefundApplyOrder $applyOrder){
        if($applyOrder->status != "unconfirmed"){
            throw new \Exception("非申请状态无法执行确认操作");
        }
        $applyOrder->status = "confirmed";
    }

    /**
     * 拒绝操作
     * @return array
     */
    private function refuse(HotelRefundApplyOrder $applyOrder, HotelOrder $order){
        if($applyOrder->status != "confirmed"){
            throw new \Exception("非确认状态无法执行拒绝操作");
        }
        $applyOrder->status = "refused";
        $order->pay_status = "paid";
    }

    /**
     * 退款操作
     * @return array
     */
    private function paid(HotelRefundApplyOrder $applyOrder, HotelOrder $order){
        if($applyOrder->status != "confirmed"){
            throw new \Exception("非确认状态无法执行打款操作");
        }

        $user = User::findOne($order->user_id);
        if(!$user || $user->is_delete){
            throw new \Exception("用户[ID:".$order->user_id."]不存在");
        }

        $applyOrder->status = "paid";
        $order->pay_status = "refund"; //设置订单为已退款
        if($order->pay_type == "integral"){ //红包退款
            $applyOrder->refund_price = $order->integral_deduction_price; //设置退款红包数量
            $res = UserIntegralForm::hotelOrderRefundAdd($applyOrder, $user);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
        }else{
            throw new \Exception("暂未支持除红包支付外的退款");
        }
    }


}