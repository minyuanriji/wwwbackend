<?php
namespace app\component\efps\lib\pay;


use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\ParamsBuilder;

class WxJSAPIPayment extends ParamsBuilder implements InterfaceEfps{

    public function getApi(){
        return "/api/txs/pay/WxJSAPIPayment";
    }

    public function build($params){

        $this->params = array_merge($params, [
            "payCurrency" => "CNY",
            "transactionStartTime" => date("YmdHis"),
            "nonceStr" => uniqid(),
        ]);

        return $this;
    }
}