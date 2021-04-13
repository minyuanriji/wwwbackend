<?php


namespace app\component\efps\lib\pay;


use app\component\efps\lib\InterfaceEfps;
use app\component\efps\lib\ParamsBuilder;

class Refund extends ParamsBuilder implements InterfaceEfps{

    public function getApi(){
        return "/api/txs/pay/Refund/V2";
    }

    public function build($params){

        $this->params = array_merge($params, [
            "nonceStr" => md5(uniqid())
        ]);

        return $this;
    }
}