<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\plateform\result\SubmitResult;

class SubmitFastAction extends BaseAction {

    public function run(){
        $configs = $this->getPlateConfig();

        $req = new Req($configs['host'], $configs['app_key'], $configs['app_secret']);
        $params['orderId']   = $this->orderModel->order_no;
        $params['mobile']    = $this->orderModel->mobile;
        $params['amount']    = $this->orderModel->order_price;
        $params['notifyUrl'] = "https://www.mingyuanriji.cn/web/addcredit-notify/index.php";

        $res = $req->doPost("/v1/mobile/order", $params);

        $submitResult = new SubmitResult();
        $submitResult->request_data     = $res['request_data'];
        $submitResult->response_content = $res['response_content'];
        $submitResult->code             = $res['code'] == Code::SUCCESS ? SubmitResult::CODE_SUCC : SubmitResult::CODE_FAIL;
        $submitResult->message          = $res['message'];

        return $submitResult;
    }


}