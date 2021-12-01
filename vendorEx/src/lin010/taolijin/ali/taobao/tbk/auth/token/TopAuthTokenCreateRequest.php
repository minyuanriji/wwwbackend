<?php

namespace lin010\taolijin\ali\taobao\tbk\auth\token;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TopAuthTokenCreateRequest extends TbkBaseRequest {


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.top.auth.token.create";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check() {
        // TODO: Implement check() method.
    }
}