<?php

namespace lin010\taolijin\ali\taobao\tbk\tlj;

use lin010\taolijin\ali\taobao\tbk\abstracts\TbkBaseResponse;

class TbkDgVegasTljCreateResponse extends TbkBaseResponse {

    public function setCode(){
        parent::setCode();
        $result = $this->result;
        if(isset($result->result)){
            $result = $result->result;
            if($result->msg_code){
                $result = (array)$result;
                $this->code = $result['msg_code'];
                $this->msg = $result['msg_info'];
            }
        }
    }

    public function getModelData(){
        $model = @json_decode(@json_encode($this->result), true);
        return $model['result']['model'];
    }
}