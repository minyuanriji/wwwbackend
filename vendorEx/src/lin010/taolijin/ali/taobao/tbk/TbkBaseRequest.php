<?php


namespace lin010\taolijin\ali\taobao\tbk;

abstract class TbkBaseRequest{

    protected $apiParas = [];

    public function __set($name, $value){
        $this->apiParas[$name] = $value;
    }

    /**
     * 返回API参数
     * @return array
     */
    public function getApiParas(){
        return $this->apiParas;
    }


    /**
     * 获取方法名
     * @return string
     */
    abstract public function getApiMethodName();

    /**
     * 参数检查
     * @throws \Exception
     */
    abstract public function check();

}