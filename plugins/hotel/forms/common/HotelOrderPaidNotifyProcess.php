<?php

namespace app\plugins\hotel\forms\common;

use app\core\ApiCode;
use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;

class HotelOrderPaidNotifyProcess extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $hotelOrder = HotelOrder::findOne(["order_no" => $paymentOrder->orderNo]);
        if(!$hotelOrder){
            throw new \Exception("订单{$paymentOrder->orderNo}不存在");
        }

        if(!OrderHelper::isPayable($hotelOrder)){
            throw new \Exception("订单{$paymentOrder->orderNo}状态异常");
        }

        //平台下单
        $plateform = $hotelOrder->getPlateform();
        if(!$plateform){
            throw new \Exception("无法获取平台信息");
        }

        //TODO 第一次调用第三方平台下单接口
        if($plateform->source_code == $plateform->plateform_code){
            /*$res = OrderHelper::submitPlateformOrder($hotelOrder, $plateform);
            if($res['code'] != ApiCode::CODE_SUCCESS){
                throw new \Exception($res['msg']);
            }
            $plateform->plateform_code = $res['data']['plateform_order_no'];
            if(!$plateform->save()){
                throw new \Exception(json_encode($plateform->getErrors()));
            }*/
        }

        //更新订单
        switch ($paymentOrder->payType) {
            case PaymentOrder::PAY_TYPE_BALANCE:
                $hotelOrder->pay_type = "balance";
                break;
            case PaymentOrder::PAY_TYPE_WECHAT:
                $hotelOrder->pay_type = "wechat";
                break;
            case PaymentOrder::PAY_TYPE_ALIPAY:
                $hotelOrder->pay_type = "alipay";
                break;
            default:
                break;
        }

        //TODO 测试 后面要改回来
        $hotelOrder->order_status = "success";
        //$hotelOrder->order_status = "unconfirmed";
        $hotelOrder->pay_status   = "paid";
        $hotelOrder->pay_at       = date("Y-m-d H:i:s");
        $hotelOrder->pay_price    = $paymentOrder->amount;
        if(!$hotelOrder->save()){
            throw new \Exception($this->responseErrorMsg($hotelOrder));
        }

    }
}