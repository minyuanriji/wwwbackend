<?php
namespace app\component\efps\lib\pay;


use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\ParamsBuilder;

class AliJSAPIPayment extends ParamsBuilder implements InterfaceEfps{


    public function getApi(){
        return "/api/txs/pay/NativePayment";
    }

    public function build($params){

        $this->params = array_merge($params, [
            "payMethod" => "7",
            "transactionStartTime" => date("YmdHis"),
            "nonceStr" => uniqid(),
            "payCurrency" => "CNY"
        ]);

        return $this;
    }
}