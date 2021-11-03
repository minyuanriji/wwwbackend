<?php

namespace app\plugins\oil\forms\api;

use app\core\ApiCode;
use app\forms\common\UserIntegralModifyForm;
use app\plugins\oil\models\OilOrders;

class OilDoSubmitForm extends OilBaseSubmitForm {

    public function doSubmit(){

        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $t = \Yii::$app->db->beginTransaction();
        try {

            $data = $this->buildOrderData();

            $order = new OilOrders([
                "mall_id"                  => $data['order_data']['mall_id'],
                "user_id"                  => $data['order_data']['user_id'],
                "product_id"               => $data['order_data']['product_id'],
                "order_no"                 => generate_order_no("OIL"),
                "order_status"             => "unpaid",
                "order_price"              => $data['order_data']['order_price'],
                "created_at"               => time(),
                "updated_at"               => time(),
                "pay_status"               => "unpaid",
                "integral_deduction_price" => $data['order_data']['integral_deduction_price'],
                "integral_fee_rate"        => $data['order_data']['integral_fee_rate']
            ]);
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            //如果使用红包支付，扣除红包
            if($order['integral_deduction_price'] > 0){
                $modifyForm = new UserIntegralModifyForm([
                    "type"        => 2,
                    "integral"    => $order['integral_deduction_price'],
                    "desc"        => "加油券订单红包抵扣",
                    "source_id"   => $order->id,
                    "source_type" => "oil_order",
                    "is_manual"   => 0
                ]);
                $modifyForm->modify($data['user']);
            }

            $t->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    "order_id" => $order->id,
                    "order_no" => $order->order_no
                ]
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