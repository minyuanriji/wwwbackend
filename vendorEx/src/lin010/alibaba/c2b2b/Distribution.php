<?php

namespace lin010\alibaba\c2b2b;

use lin010\alibaba\c2b2b\api\BaseAPI;

class Distribution{

    private $appKey;
    private $secret;

    private $gateway = "http://gw.open.1688.com/openapi/";

    public function __construct($appKey, $secret){
        $this->appKey = $appKey;
        $this->secret = $secret;
    }

    public function request(BaseAPI $api){

        $params = $api->getParams();
        $params['_aop_signature'] = $this->getSign($api);

        $ch = curl_init("{$this->gateway}param2/{$api->version()}/{$api->getPath()}/{$this->appKey}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $response = $api->getResponse();

        try {
            $content = curl_exec($ch);
            $error  = curl_error($ch);
            if(!empty($error)){
                throw new \Exception($error);
            }
            $content = "";
            $response->setResult($content);
        }catch (\Exception $e){
            $response->setError($e->getMessage(), -1);
        }
        print_r($response);
        exit;
        return $response;
    }

    public function requestWithToken(BaseAPI $api, $token){
        $api->setParam("access_token", $token, true);
        return $this->request($api);
    }

    /**
     * 获取签名
     * @param BaseAPI $api
     * @return string
     */
    public function getSign(BaseAPI $api){
        $path = "param2/{$api->version()}/{$api->getPath()}/{$this->appKey}";

        $params = $api->getParams();

        $stringArray = [];
        foreach($params as $key => $val){
            $stringArray[] = $key . $val;
        }

        //按照首字母排序
        sort($stringArray);

        $pathAndParamString = $path . implode("", $stringArray);

        $signSha1String = hash_hmac ( "sha1", $pathAndParamString, $this->secret, true );
        $signHexWithLowcase = bin2hex($signSha1String);
        $signHexUppercase = strtoupper($signHexWithLowcase);

        return $signHexUppercase;
    }

}