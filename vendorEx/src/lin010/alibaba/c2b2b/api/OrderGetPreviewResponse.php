<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class OrderGetPreviewResponse extends Response {

    public $result;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $this->result = isset($result['orderPreviewResuslt']) ? $result['orderPreviewResuslt'] : [];
    }
}