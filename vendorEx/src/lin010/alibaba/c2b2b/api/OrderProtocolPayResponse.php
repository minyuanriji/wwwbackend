<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class OrderProtocolPayResponse extends Response {

    public $success;
    public $code;
    public $message;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $this->message = isset($result['message']) ? $result['message'] : "支付宝协议代扣失败";
        $this->code    = isset($result['code']) ? $result['code'] : "-1";
        $this->success = isset($result['success']) ? (int)$result['success'] : 0;
    }
}