<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinOrders;

class TaoLiJinOrderDoFinishForm extends BaseModel{

    public $order_id;

    public function rules(){
        return [
            [['order_id'], 'required']
        ];
    }

    public function doFinish(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $order = TaolijinOrders::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单[ID:{$this->order_id}]不存在");
            }

            $statusInfo = TaolijinOrders::getStatusInfo($order->order_status, $order->pay_status);
            if($statusInfo['status'] != "paid"){
                throw new \Exception("状态异常！无法操作");
            }

            $order->order_status = "finished";
            $order->updated_at = time();
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg'  => '保存成功'
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }
}