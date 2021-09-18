<?php

namespace lin010\alibaba\c2b2b\api;

use lin010\alibaba\c2b2b\Response;

abstract class BaseAPI
{
    private $params = [];

    public function __construct($params = []){
        foreach($params as $key => $value){
            $this->setParam($key, $value);
        }
    }

    /**
     * 版本
     * @return int
     */
    public function version(){
        return 1;
    }

    /**
     * 获取结果对象
     * @return Response
     */
    abstract public function getResponse();

    /**
     * 获取路径
     * @return string
     */
    abstract public function getPath();

    /**
     * 是否需要授权
     * @return boolean
     */
    abstract public function needAuth();

    /**
     * 返回API参数
     * @return array
     */
    abstract public static function paramKeys();

    /**
     * 设置API参数
     * @param string $key
     * @param string $value
     * @param boolean $force
     * @return void
     */
    public function setParam($key, $value, $force = false){
        $paramKeys = static::paramKeys();
        if(in_array($key, $paramKeys) || $force){
            $this->params[$key] = $value;
            $this->$key = $value;
        }
    }

    /**
     * 返回API参数
     * @return array
     */
    public function getParams(){
        return $this->params;
    }
}