<?php

namespace lin010\taolijin\ali\taobao\tbk\cat;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class ItemcatsGetRequest extends TbkBaseRequest{


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName()
    {
        return "taobao.itemcats.get";
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