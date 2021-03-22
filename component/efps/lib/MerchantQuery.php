<?php


namespace app\component\efps\lib;


class MerchantQuery implements InterfaceEfps{

    public $params;

    public function getParam(){
        return $this->params;
    }

    public function getApi(){
        return "/api/cust/SP/Merchant/query";
    }

    public function build($params){
        if(empty($params['acqMerId'])){
            throw new \Exception("商户编号[acqMerId]未设置");
        }
        $this->params = [
            'acqMerId' => $params['acqMerId']
        ];
        return $this;
    }
}