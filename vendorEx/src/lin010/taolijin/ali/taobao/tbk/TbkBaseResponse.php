<?php

namespace lin010\taolijin\ali\taobao\tbk;

abstract class TbkBaseResponse{

    public $code = null; //请求失败返回的错误码
    public $msg; //请求失败返回的错误信息

    protected $result;

    public function __construct($result){
        $this->result = $result;
        $this->code = isset($result->code) ? $result->code : "";
        $this->msg = isset($result->msg) ? $result->msg : "";
    }
}