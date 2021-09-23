<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetCategoryInfoResponse extends Response {

    public $result;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result){
        $this->result = isset($result['categoryInfo']) ? $result['categoryInfo'][0] : [];
    }

    /**
     * 获取父分类ID
     * @return mixed|null
     */
    public function getParentID(){
        return isset($this->result['parentIDs']) && !empty($this->result['parentIDs']) ? $this->result['parentIDs'][0] : null;
    }
}