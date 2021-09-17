<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class OrderCreateResponse extends Response {

    public $orderId;
    public $totalSuccessAmount;
    public $postFee;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $result = isset($result['result']) ? $result['result'] : [];
        if(!isset($result['orderId'])){
            throw new \Exception("无法获取“orderId”参数");
        }
        $this->orderId = $result['orderId'];
        $this->totalSuccessAmount = $result['totalSuccessAmount'];
        $this->postFee = $result['postFee'];
    }
}