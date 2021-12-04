<?php

namespace lin010\taolijin\ali\taobao\tbk\material;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkDgMaterialOptionalResponse extends TbkBaseResponse {

    public function getData(){
        $resultList = isset($this->result->result_list) ? json_decode(json_encode($this->result->result_list), true) : [];
        $data['list'] = isset($resultList['map_data']) ? $resultList['map_data'] : [];
        $data['count'] = isset($this->result->total_results) ? (int)$this->result->total_results : 0;
        return $data;
    }

}