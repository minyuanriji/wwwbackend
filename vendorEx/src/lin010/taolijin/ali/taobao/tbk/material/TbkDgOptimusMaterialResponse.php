<?php

namespace lin010\taolijin\ali\taobao\tbk\material;

use lin010\taolijin\ali\taobao\tbk\TbkBaseResponse;

class TbkDgOptimusMaterialResponse extends TbkBaseResponse{

    /**
     * 返回数据列表
     * @return array
     */
    public function getMapData(){
        $result = @json_decode(@json_encode(@simplexml_load_string($this->result->asXML())), true);
        if(isset($result['result_list']) && isset($result['result_list']['map_data'])){
            $mapData = $result['result_list']['map_data'];
        }else{
            $mapData = [];
        }
        return $mapData;
    }

}