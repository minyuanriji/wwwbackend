<?php

namespace lin010\taolijin;

class TbkDgOptimusMaterialRequest{

    private $apiParas = [];

    public function __set($name, $value){
        $this->apiParas[$name] = $value;
    }

    public function getApiMethodName(){
        return "taobao.tbk.dg.optimus.material";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){

    }
}