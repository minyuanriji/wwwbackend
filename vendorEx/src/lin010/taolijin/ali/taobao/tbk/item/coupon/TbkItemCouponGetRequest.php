<?php

namespace lin010\taolijin\ali\taobao\tbk\item\coupon;


use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseRequest;

class TbkItemCouponGetRequest extends TbkBaseRequest {

    /**
     * 获取方法名
     * @return string
     */
    public function getApiMethodName(){
        return "taobao.tbk.coupon.get";
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