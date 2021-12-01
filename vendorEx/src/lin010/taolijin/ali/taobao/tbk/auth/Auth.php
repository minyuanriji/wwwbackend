<?php

namespace lin010\taolijin\ali\taobao\tbk\auth;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenCreateRequest;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenCreateResponse;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenRefreshRequest;
use lin010\taolijin\ali\taobao\tbk\auth\token\TopAuthTokenRefreshResponse;

class Auth extends TbkBaseHandle {

    /**
     * 获取TOKEN
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function getToken($params = []){
        $bestUrl = "https://eco.taobao.com/router/rest";
        return parent::client(TopAuthTokenCreateRequest::class, $params)->execute(TopAuthTokenCreateResponse::class, null, $bestUrl);
    }

    /**
     * 刷新TOKEN
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function refreshToken($params = []){
        $bestUrl = "https://eco.taobao.com/router/rest";
        return parent::client(TopAuthTokenRefreshRequest::class, $params)->execute(TopAuthTokenRefreshResponse::class, null, $bestUrl);
    }
}