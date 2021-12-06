<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkScPublisherInfoSaveRequest extends TbkBaseRequest {


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName()
    {
        return "taobao.tbk.sc.publisher.info.save";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check()
    {
        // TODO: Implement check() method.
    }
}