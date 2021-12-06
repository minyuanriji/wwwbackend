<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseHandle;

class Publisher extends TbkBaseHandle {

    /**
     * 私域用户备案
     * @param string $session
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function save($session, $params = []){
        return parent::client(TbkScPublisherInfoSaveRequest::class, $params)->execute(TbkScPublisherInfoSaveResponse::class, $session);
    }

    /**
     * 私域用户备案信息查询
     * @param string $session
     * @param array $params
     * @return TbkBaseResponse
     * @throws \Exception
     */
    public function get($session, $params = []){
        return parent::client(TbkScPublisherInfoGetRequest::class, $params)->execute(TbkScPublisherInfoGetResponse::class, $session);
    }
}