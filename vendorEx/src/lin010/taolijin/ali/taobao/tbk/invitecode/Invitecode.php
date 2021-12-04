<?php

namespace lin010\taolijin\ali\taobao\tbk\invitecode;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Invitecode extends TbkBaseHandle {

    /**
     * 私域用户邀请码生成
     * @param string $session
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function getInviteCode($session, $params = []){
        return parent::client(TbkScInvitecodeGetRequest::class, $params)->execute(TbkScInvitecodeGetResponse::class, $session);
    }

}