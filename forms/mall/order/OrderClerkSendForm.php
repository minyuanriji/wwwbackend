<?php
namespace app\forms\mall\order;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\Order;
use app\models\OrderClerk;
use app\models\OrderDetail;

class OrderClerkSendForm extends BaseModel{

    public $clerk_id;
    public $send_type;
    public $express;
    public $express_code;
    public $express_no;
    public $express_content;

    public function rules(){
        return [
            [['clerk_id', 'send_type'], 'required'],
            [['express', 'express_code', 'express_no', 'express_content'], 'safe']
        ];
    }

    public function send(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $orderClerk = OrderClerk::findOne($this->clerk_id);
            if(!$orderClerk || $orderClerk->is_delete){
                throw new \Exception("核销记录不存在");
            }

            $order = Order::findOne($orderClerk->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单不存在");
            }

            if(!$order->is_pay){
                throw new \Exception("订单未支付");
            }

            $orderDetails = OrderDetail::findAll(["is_delete" => 0, "order_id" => $order->id, "is_refund" => 0]);
            if(!$orderDetails){
                throw new \Exception("订单详情记录不存在");
            }

            foreach($orderDetails as $orderDetail){
                $form = new OrderClerkExpressForm([
                    "order_id"        => $order->id,
                    "order_detail_id" => $orderDetail->id,
                    "goods_id"        => $orderDetail->goods_id,
                    "send_type"       => $this->send_type,
                    "express_no"      => $this->express_no,
                    "express_content" => $this->express_content,
                    "express"         => $this->express,
                    "express_code"    => $this->express_code,
                    "store_id"        => $order->store_id
                ]);
                $res = $form->save();
                if($res['code'] != ApiCode::CODE_SUCCESS){
                    throw new \Exception($res['msg']);
                }
            }

            $orderClerk->express_status = 1;
            $orderClerk->updated_at     = time();
            if(!$orderClerk->save()){
                throw new \Exception($this->responseErrorMsg($orderClerk));
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