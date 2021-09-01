<?php

namespace lin010\taolijin\ali\taobao\tbk\item\convert;


use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkItemConvertRequest extends TbkBaseRequest {

    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.item.convert";
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