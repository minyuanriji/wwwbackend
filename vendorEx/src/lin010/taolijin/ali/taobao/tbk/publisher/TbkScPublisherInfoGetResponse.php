<?php

namespace lin010\taolijin\ali\taobao\tbk\publisher;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkScPublisherInfoGetResponse extends TbkBaseResponse {

    public function getResult(){
        $data = isset($this->result->data) ? $this->result->data : null;
        $inviterList = $data && isset($data->inviter_list) ? (array)$data->inviter_list : null;
        $inviterList = $inviterList && isset($inviterList['map_data']) ? $inviterList['map_data'] : null;
        $totalCount = $data && isset($data->total_count) ? (array)$data->total_count : null;

        $list = [];
        if($inviterList){
            foreach($inviterList as $item){
                $list[] = (array)$item;
            }
        }

        return [
            'list' => $list,
            'count' => $totalCount ? $totalCount[0] : 0
        ];
    }

}