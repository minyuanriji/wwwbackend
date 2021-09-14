<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetCategoryListResponse extends Response{

    public $list = [];

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result) {
        if(!isset($result['categoryInfo'])){
            $this->setError("参数[categoryInfo]未获取到");
            return;
        }
        $categoryInfo = array_shift($result['categoryInfo']);
        if(!isset($categoryInfo['childCategorys'])){
            $this->setError("参数[childCategorys]未获取到");
            return;
        }
        $this->list = $categoryInfo['childCategorys'];
    }
}