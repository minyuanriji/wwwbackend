<?php
namespace app\component\efps\lib\pay;

use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\ParamsBuilder;

class UnifiedPayment extends ParamsBuilder implements InterfaceEfps{

    public function getApi(){
        return "/api/txs/pay/UnifiedPayment";
    }

    public function build($params){

        $this->params = array_merge($params, [
            "payCurrency"           => "CNY",
            "transactionStartTime"  => date("YmdHis"),
            "enablePayChannels"     => "",
            "instalmentsNum"        => "3",
            "nonceStr"              => uniqid()
        ]);

        return $this;
    }

}