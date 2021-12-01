<?php

namespace lin010\taolijin\ali\taobao\tbk\auth;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenCreateRequest;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenCreateResponse;

class Auth extends TbkBaseHandle {

    /**
     * 获取TOKEN
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function getToken($params = []){
        return parent::client(TopAuthTokenCreateRequest::class, $params)->execute(TopAuthTokenCreateResponse::class);
    }

}