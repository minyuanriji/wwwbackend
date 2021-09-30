<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetRefundReasonListResponse extends Response{

    public $reasons;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $result = isset($result['result']) ? $result['result'] : [];
        $result = isset($result['result']) ? $result['result'] : [];
        $this->reasons = isset($result['reasons']) ? $result['reasons'] : [];
    }
}