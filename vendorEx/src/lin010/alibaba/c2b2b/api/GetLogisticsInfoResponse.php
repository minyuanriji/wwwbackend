<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetLogisticsInfoResponse extends Response{

    public $result;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result) {
        if(isset($result['success']) && $result['success'] == 1){
            $this->result = $result['result'][0];
        }else{
            $this->setError("查询失败");
        }
    }
}