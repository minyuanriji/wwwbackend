<?php

namespace lin010\taolijin\ali\taobao\tbk\spread;


use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkSpreadGetRequest extends TbkBaseRequest {

    public function getApiMethodName(){
        return "taobao.tbk.spread.get";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check(){
        // TODO: Implement check() method.
    }
}
