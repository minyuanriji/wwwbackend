<?php

namespace lin010\alibaba\c2b2b;

abstract class Response
{
    const SUCCESS_CODE = 0;

    public $code;
    public $error;

    public $originResultContent = "";

    /**
     * 设置数据
     * @param array $result
     * @return void
     */
    abstract protected function setData($result);

    /**
     * 设置错误
     * @param string $message
     * @params integer $code
     * @return void
     */
    public function setError($message, $code = -1){
        $this->error = $message;
        $this->code  = $code;
    }

    /**
     * 解析结果数据
     * @param string $content
     * @return void
     */
    public function setResult($content){
        $this->originResultContent = $content;
        $result = (array)@json_decode($content, true);

        if(isset($result['errorMsg'])){
            $this->setError($result['errorMsg'], isset($result['errorCode']) ? $result['errorCode'] : -1);
            return;
        }

        if(isset($result['error_message'])){
            $this->setError($result['error_message'], isset($result['error_code']) ? $result['error_code'] : -1);
            return;
        }
        $this->setData($result);
    }
}