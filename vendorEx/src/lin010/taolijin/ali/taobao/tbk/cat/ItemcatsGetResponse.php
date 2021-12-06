<?php

namespace lin010\taolijin\ali\taobao\tbk\cat;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class ItemcatsGetResponse extends TbkBaseResponse {

    public function getResult(){
        print_r($this->result);
        exit;
    }

}