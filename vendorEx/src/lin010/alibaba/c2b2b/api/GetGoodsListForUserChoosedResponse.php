<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetGoodsListForUserChoosedResponse extends Response{

    public $rows;
    public $totalCount;

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    protected function setData($result) {
        $result = isset($result['result'])  && is_array($result['result']) ? $result['result'] : [];
        $this->rows       = isset($result['resultList']) && is_array($result['resultList']) ? $result['resultList'] : [];
        $this->totalCount = isset($result['totalRow']) ? $result['totalRow'] : 0;
    }
}