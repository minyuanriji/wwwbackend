<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

class GetOrderInfo extends BaseAPI {


    /**
     * 获取结果对象
     * @return Response
     */
    public function getResponse(){
        return new GetOrderInfoResponse();
    }

    /**
     * 获取路径
     * @return string
     */
    public function getPath(){
        return "com.alibaba.trade/alibaba.trade.get.buyerView";
    }

    /**
     * 是否需要授权
     * @return boolean
     */
    public function needAuth(){
        return true;
    }

    /**
     * 返回API参数
     * @return array
     */
    public static function paramKeys(){
        return ["webSite", "orderId"];
    }
}