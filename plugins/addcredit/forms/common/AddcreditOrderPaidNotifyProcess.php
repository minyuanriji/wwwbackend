<?php

namespace app\plugins\addcredit\forms\common;

use app\core\ApiCode;
use app\core\payment\PaymentNotify;
use app\core\payment\PaymentOrder;
use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;
use app\plugins\addcredit\plateform\sdk\kcb_sdk\PlateForm as kcb_PlateForm;

class AddcreditOrderPaidNotifyProcess extends PaymentNotify{


    /**
     * @param PaymentOrder $paymentOrder
     * @return mixed
     */
    public function notify($paymentOrder){

        $AddcreditOrder = AddcreditOrder::findOne(["order_no" => $paymentOrder->orderNo]);
        if(!$AddcreditOrder){
            throw new \Exception("订单{$paymentOrder->orderNo}不存在");
        }

        if($AddcreditOrder->pay_status != 'unpaid' || $AddcreditOrder->order_status != 'unpaid'){
            throw new \Exception("订单{$paymentOrder->orderNo}状态异常");
        }

        //平台下单
        $plateform = AddcreditPlateforms::findOne($AddcreditOrder->plateform_id);
        if (!$plateform) {
            throw new \Exception("无法获取平台信息");
        }

        $plate_form = new kcb_PlateForm();
        $submit_res = $plate_form->submit($AddcreditOrder, $plateform);

        if (!$submit_res) {
            throw new \Exception('未知错误！');
        }
        if ($submit_res->code != ApiCode::CODE_SUCCESS) {
            throw new \Exception($submit_res->message);
        }

        //更新订单状态为已支付
        $AddcreditOrder->order_status = "processing";
        $AddcreditOrder->pay_status = "paid";
        $AddcreditOrder->pay_at = time();
        $AddcreditOrder->plateform_request_data = $submit_res->request_data;
        $AddcreditOrder->plateform_response_data = $submit_res->response_content;
        if (!$AddcreditOrder->save()) {
            throw new \Exception($this->responseErrorMsg($AddcreditOrder));
        }
    }
}