<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkScPublisherInfoSaveResponse extends TbkBaseResponse {

    public function getSpecialId(){
        $result = @json_decode(json_encode($this->result), true);
        return isset($result['data']) ? $result['data']['special_id'] : null;
    }

}