<?php

namespace lin010\taolijin\ali\taobao\tbk\auth\token;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TopAuthTokenRefreshResponse extends TbkBaseResponse{

    public function getTokenData(){
        $tokenResult = isset($this->result->token_result) ? $this->result->token_result : [];
        return (array)@json_decode($tokenResult, true);
    }
}