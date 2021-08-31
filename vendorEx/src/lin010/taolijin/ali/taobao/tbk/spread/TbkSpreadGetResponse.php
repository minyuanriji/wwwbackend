<?php

namespace lin010\taolijin\ali\taobao\tbk\spread;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkSpreadGetResponse extends TbkBaseResponse {

    public function getContent(){
        $result = json_decode(json_encode($this->result), true);
        return $result['results']['tbk_spread']['content'];
    }
}