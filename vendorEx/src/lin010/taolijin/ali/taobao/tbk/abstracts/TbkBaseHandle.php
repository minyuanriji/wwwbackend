<?php

namespace lin010\taolijin\ali\taobao\tbk\abstracts;

use lin010\taolijin\Ali;

abstract class TbkBaseHandle{

    protected $ali;

    protected $request;

    public function __construct(Ali $ali){
        $this->ali = $ali;
    }

    public function client($class, $params = []){
        $this->request = new $class();
        if(!($this->request instanceof TbkBaseRequest)){
            throw new \Exception("{$class}必须继承TbkBaseRequest类");
        }
        foreach($params as $key => $val){
            $this->request->$key = $val;
        }
        return $this;
    }

    public function execute($class){
        $result = $this->ali->getClient()->execute($this->request);
        return new $class($result);
    }
}