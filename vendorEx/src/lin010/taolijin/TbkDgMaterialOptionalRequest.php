<?php

namespace lin010\taolijin;

class TbkDgMaterialOptionalRequest{

    private $apiParas = array();

    public function __set($name, $value){
        $this->apiParas[$name] = $value;
    }

    public function getApiMethodName(){
        return "taobao.tbk.dg.material.optional";
    }

    public function getApiParas(){
        return $this->apiParas;
    }

    public function check(){
        $requires = ["adzone_id", "cat", "q"];
        foreach($requires as $p){
            if(!isset($this->apiParas[$p]) || empty($this->apiParas[$p])){
                throw new \Exception("{$p}参数不能为空");
            }
        }
    }
}