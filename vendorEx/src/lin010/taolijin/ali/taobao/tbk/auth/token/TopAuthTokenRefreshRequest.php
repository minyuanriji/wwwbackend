<?php

namespace lin010\taolijin\ali\taobao\tbk\auth\token;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TopAuthTokenRefreshRequest extends TbkBaseRequest {

    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.top.auth.token.refresh";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check() {
        // TODO: Implement check() method.
    }

}