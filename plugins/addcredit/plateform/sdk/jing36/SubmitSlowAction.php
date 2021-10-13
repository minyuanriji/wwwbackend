<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\plateform\result\SubmitResult;

class SubmitSlowAction extends BaseAction {

    public function run(){
        $configs = @json_decode($this->plateModel->json_param, true);
        $appKey = isset($configs['app_key']) ? $configs['app_key'] : "";
        $appSecret = isset($configs['app_secret']) ? $configs['app_secret'] : "";

        $req = new Req($configs['host'], $appKey, $appSecret);
        $params['orderId']   = $this->orderModel->id;
        $params['mobile']    = $this->orderModel->mobile;
        $params['amount']    = $this->orderModel->order_price;
        $params['notifyUrl'] = "https://www.mingyuanriji.cn";

        $res = $req->doPost("/v1/mobile/sloworder", $params);

        $submitResult = new SubmitResult();
        $submitResult->request_data     = $res['request_data'];
        $submitResult->response_content = $res['response_content'];
        $submitResult->code             = $res['code'] == Code::SUCCESS ? SubmitResult::CODE_SUCC : SubmitResult::CODE_FAIL;
        $submitResult->message          = $res['message'];

        return $submitResult;
    }


}