<?php

namespace lin010\taolijin\ali\taobao\tbk\item\info;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkItemInfoGetResponse extends TbkBaseResponse{

    public function getResult(){
        $results = isset($this->result->results) ? @json_decode(@json_encode($this->result->results), true) : [];
        return isset($results['n_tbk_item']) ? $results['n_tbk_item'] : [];
    }
}