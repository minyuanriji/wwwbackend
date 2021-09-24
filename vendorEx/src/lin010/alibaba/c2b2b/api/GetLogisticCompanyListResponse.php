<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetLogisticCompanyListResponse extends Response{


    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        echo var_export($result['result'], true);
        exit;
    }
}