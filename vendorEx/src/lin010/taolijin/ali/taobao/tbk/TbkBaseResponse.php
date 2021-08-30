<?php

namespace lin010\taolijin\ali\taobao\tbk;

abstract class TbkBaseResponse{

    public $code = null; //请求失败返回的错误码
    public $msg; //请求失败返回的错误信息

    protected $result;

    public function __construct($result){
        $this->result = $result;
        $this->setCode();
    }

    protected function setCode(){
        $this->code = isset($this->result->code) ? $this->result->code : "";
        $this->msg  = isset($this->result->msg) ? $this->result->msg : "";
    }
}