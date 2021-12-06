<?php

namespace lin010\taolijin\ali\taobao\tbk\material;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkDgMaterialOptionalRequest extends TbkBaseRequest {


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.dg.material.optional";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check(){
        // TODO: Implement check() method.
    }
}