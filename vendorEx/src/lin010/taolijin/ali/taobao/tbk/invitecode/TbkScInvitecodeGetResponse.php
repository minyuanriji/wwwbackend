<?php

namespace lin010\taolijin\ali\taobao\tbk\invitecode;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkScInvitecodeGetResponse extends TbkBaseResponse {

    public function getCode(){
        $data = isset($this->result->data) ? json_decode(json_encode($this->result->data), true) : [];
        return isset($data['inviter_code']) ? $data['inviter_code'] : null;
    }

}