<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkScPublisherInfoGetResponse extends TbkBaseResponse {

    public function getSpecialId(){
        print_r($this->result);
        exit;
    }

}