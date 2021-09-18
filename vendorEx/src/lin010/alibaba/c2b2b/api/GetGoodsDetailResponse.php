<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetGoodsDetailResponse extends Response{

    public $bizGroupInfos;
    public $productInfo;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $this->bizGroupInfos = isset($result['bizGroupInfos']) ? $result['bizGroupInfos'] : [];
        $this->productInfo = isset($result['productInfo']) ? $result['productInfo'] : [];
        if(!isset($this->productInfo['skuInfos'])){
            $this->productInfo['skuInfos'] = [];
        }
    }
}