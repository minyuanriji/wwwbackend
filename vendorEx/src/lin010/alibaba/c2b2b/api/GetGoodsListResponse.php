<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetGoodsListResponse extends Response {

    public $totalCount = 0;
    public $goodsList = [];

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $result = $result['result'];
        if(!isset($result['success']) && $result['success'] == 1){
            $this->setError("参数[success]未获取到");
            return;
        }
        $this->goodsList = $result['result'];
        $this->totalCount = $result['totalCount'];
    }
}