<?php

namespace lin010\taolijin\ali\taobao\tbk\order;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkDgNewuserOrderGetRequest extends TbkBaseRequest {


    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.order.details.get";
    }

    /**
     * 参数检查
     * @throws \Exception
     */
    public function check(){
        $reqParam = ["position_index", "page_size", "page_no", "start_time", "end_time"];
        foreach($reqParam as $para){
            if(!isset($this->apiParas[$para])){
                throw new \Exception("参数{$para}必传", self::CODE_FAIL);
            }
        }
    }
}