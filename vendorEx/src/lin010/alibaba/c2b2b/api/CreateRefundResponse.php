<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class CreateRefundResponse extends Response {

    public $refundId;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result)
    {
        $result = isset($result['result']) ? $result['result'] : [];

        if(isset($result['message']) && !empty($result['message'])){
            $this->setError($result['message']);
            return;
        }

        $this->refundId = isset($result['refundId']) ? $result['refundId'] : 0;
        if(!$this->refundId){
            $this->setError("返回结果异常");
        }
    }
}