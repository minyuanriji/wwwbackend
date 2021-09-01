<?php

namespace lin010\taolijin\ali\taobao\tbk\item\info;


use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkItemInfoGetRequest extends TbkBaseRequest {

    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.item.info.get";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check()
    {
        // TODO: Implement check() method.
    }
}