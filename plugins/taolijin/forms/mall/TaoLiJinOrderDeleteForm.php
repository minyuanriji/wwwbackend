<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinOrders;

class TaoLiJinOrderDeleteForm extends BaseModel{

    public $order_id;

    public function rules(){
        return array_merge(parent::rules(), [
            [['order_id'], 'required']
        ]);
    }

    public function delete(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $order = TaolijinOrders::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单[ID:{$this->order_id}]不存在");
            }

            $statusInfo = TaolijinOrders::getStatusInfo($order->order_status, $order->pay_status);
            if($statusInfo['status'] != "paid"){
                throw new \Exception("只有已支付状态，才允许删除");
            }

            $order->updated_at = time();
            $order->is_delete = 1;
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '删除成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }

    }
}