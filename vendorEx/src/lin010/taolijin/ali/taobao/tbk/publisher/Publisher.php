<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Publisher extends TbkBaseHandle {

    /**
     * 私域用户邀请码生成
     * @param string $session
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function save($session, $params = []){
        return parent::client(TbkScPublisherInfoSaveRequest::class, $params)->execute(TbkScPublisherInfoSaveResponse::class, $session);
    }

}