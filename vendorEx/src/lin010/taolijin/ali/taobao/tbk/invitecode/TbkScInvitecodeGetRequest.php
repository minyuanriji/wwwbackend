<?php

namespace lin010\taolijin\ali\taobao\tbk\invitecode;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkScInvitecodeGetRequest extends TbkBaseRequest
{


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName()
    {
        return "taobao.tbk.sc.invitecode.get";
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