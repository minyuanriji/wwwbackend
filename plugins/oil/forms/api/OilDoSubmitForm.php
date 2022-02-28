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
                'mobile'                   => $data['order_data']['mobile'],
                'province_id'              => $data['order_data']['province_id'],
                'province'                 => $data['order_data']['province'],
                'city_id'                  => $data['order_data']['city_id'],
                'city'                     => $data['order_data']['city'],
                'district_id'              => $data['order_data']['district_id'],
                'district'                 => $data['order_data']['district'],
                'location'                 => $data['order_data']['location'],
                'poi_type'                 => $data['order_data']['poi_type'],
                'address'                  => $data['order_data']['address'],
                "product_id"               => $data['order_data']['product_id'],
                "integral_deduction_price" => $data['order_data']['integral_deduction_price'],
                "integral_fee_rate"        => $data['order_data']['integral_fee_rate'],
                "order_price"              => $data['order_data']['order_price'],
                "order_no"                 => generate_order_no("OIL"),
                "order_status"             => "unpaid",
                "created_at"               => time(),
                "updated_at"               => time(),
                "pay_status"               => "unpaid",
            ]);
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            //如果使用金豆支付，扣除金豆
            if($order['integral_deduction_price'] > 0){
                $modifyForm = new UserIntegralModifyForm([
                    "type"        => 2,
                    "integral"    => $order['integral_deduction_price'],
                    "desc"        => "加油券订单金豆抵扣",
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