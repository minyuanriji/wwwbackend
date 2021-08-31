<?php

namespace lin010\taolijin\ali\taobao\tbk\item;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;
use lin010\taolijin\ali\taobao\tbk\item\info\TbkItemInfoGetRequest;
use lin010\taolijin\ali\taobao\tbk\item\info\TbkItemInfoGetResponse;

class Item extends TbkBaseHandle {

    /**
     * 淘宝客-公用-淘宝客商品详情查询(简版)
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function infoGet($params = []){
        return parent::client(TbkItemInfoGetRequest::class, $params)->execute(TbkItemInfoGetResponse::class);
    }

}