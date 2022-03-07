<?php

namespace lin010\taolijin\ali\taobao\tbk\item\coupon;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkItemCouponGetResponse extends TbkBaseResponse{

    public function getResult(){
        $data = $this->result && isset($this->result->data) ? (array)$this->result->data : null;
        return $data;
    }
}