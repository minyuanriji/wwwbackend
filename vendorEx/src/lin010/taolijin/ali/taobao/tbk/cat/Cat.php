<?php

namespace lin010\taolijin\ali\taobao\tbk\cat;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Cat extends TbkBaseHandle {

    /**
     * 获取类目
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function getCats($params = []){
        return parent::client(ItemcatsGetRequest::class, $params)->execute(ItemcatsGetResponse::class);
    }

}