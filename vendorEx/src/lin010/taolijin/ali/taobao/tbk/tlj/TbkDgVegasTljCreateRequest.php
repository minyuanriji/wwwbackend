<?php

namespace lin010\taolijin\ali\taobao\tbk\tlj;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

/**
 * 淘宝客-推广者-淘礼金创建
 */
class TbkDgVegasTljCreateRequest extends TbkBaseRequest {


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.dg.vegas.tlj.create";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check(){

    }
}