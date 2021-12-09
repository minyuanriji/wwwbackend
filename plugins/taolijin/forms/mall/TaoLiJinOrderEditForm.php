<?php

namespace app\plugins\taolijin\forms\mall;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\taolijin\models\TaolijinOrders;

class TaoLiJinOrderEditForm extends BaseModel{

    public $order_id;
    public $field;
    public $value;

    public function rules(){
        return array_merge(parent::rules(), [
            [['order_id', 'field', 'value'], 'required']
        ]);
    }

    public function save(){

        if (!$this->validate()) {
            return $this->responseErrorInfo();
        }

        try {

            $order = TaolijinOrders::findOne($this->order_id);
            if(!$order || $order->is_delete){
                throw new \Exception("订单[ID:{$this->order_id}]不存在");
            }

            $this->orderNotFinished($order);

            $allows = ["pay_price", "pay_at", "ali_created_at", "ali_item_id", "ali_item_name", "ali_item_pic",
                "ali_item_price", "ali_commission_rate", "ali_commission_price"
            ];
            if(!in_array($this->field, $allows)){
                throw new \Exception("不允许修改此信息");
            }

            $editMethods = [
                'ali_commission_rate' => 'editCommissionRate',
                'ali_commission_price' => 'editCommissionPrice'
            ];

            if(isset($editMethods[$this->field])){
                $method = $editMethods[$this->field];
                $this->$method($order);
            }else{
                $field = $this->field;
                $order->$field = $this->value;
            }

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

    /**
     * 设置佣金比例
     * @param TaolijinOrders $order
     * @throws \Exception
     */
    public function editCommissionRate(TaolijinOrders $order){

        $rate = (float)$this->value;
        if($rate < 0 || $rate > 100){
            throw new \Exception("佣金比例只能在0~100之间");
        }
        $order->ali_commission_rate = $rate;
    }

    /**
     * 设置佣金
     * @param TaolijinOrders $order
     */
    public function editCommissionPrice(TaolijinOrders $order){
        $price = (float)$this->value;
        $order->ali_commission_price = $price;
    }

    /**
     * @param TaolijinOrders $order
     * @throws \Exception
     */
    private function orderNotFinished(TaolijinOrders $order){
        $statusInfo = TaolijinOrders::getStatusInfo($order->order_status, $order->pay_status);
        if($statusInfo['status'] == "finished"){
            throw new \Exception("订单已结束，无法设置");
        }
    }
}