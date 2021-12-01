<?php
namespace lin010\taolijin\ali\taobao\tbk\order;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Order extends TbkBaseHandle {

    /**
     * 淘宝客-公用-淘宝客商品详情查询(简版)
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function get($params = []){
        return parent::client(TbkDgNewuserOrderGetRequest::class, $params)->execute(TbkDgNewuserOrderGetResponse::class);
    }

}