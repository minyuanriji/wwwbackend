<?php

namespace lin010\alibaba\c2b2b;

class WebOauth2{

    private $appKey;
    private $secret;
    private $redirectUri;
    private $state;

    public $error = null;

    private $tokenInfo = [
        "refresh_token" => null,
        "access_token"  => null,
        "expires_in"    => null
    ];


    /**
     * @param string $appKey
     * @param string $redirectUri
     * @param string $secret
     * @param string $state
     */
    public function __construct($appKey, $secret, $redirectUri, $state = null){
        $this->appKey      = $appKey;
        $this->secret      = $secret;
        $this->redirectUri = $redirectUri;
        $this->state       = $state ? $state : uniqid();
    }

    /**
     * 刷新凭证
     * @param string $refreshToken
     * @return string
     */
    public function refresh($refreshToken){

        $params["grant_type"]    = "refresh_token";
        $params["client_id"]     = $this->appKey;
        $params["client_secret"] = $this->secret;
        $params["refresh_token"] = $refreshToken;

        $ch = curl_init("https://gw.open.1688.com/openapi/param2/1/system.oauth2/getToken/{$this->appKey}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        try {
            $content = curl_exec($ch);
            $error  = curl_error($ch);
            if(!empty($error)){
                throw new \Exception($error);
            }
            $tokenInfo = @json_decode($content, true);
            if(is_array($tokenInfo) && isset($tokenInfo['access_token'])){
                $this->tokenInfo = [
                    "refresh_token" => $refreshToken,
                    "access_token"  => $tokenInfo['access_token'],
                    "expires_in"    => $tokenInfo['expires_in']
                ];
            }else{
                $this->error = $content;
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
        }
    }

    /**
     * 执行授权操作
     * @return string
     */
    public function auth(){
        if(preg_match("/code=.+/", $_SERVER['QUERY_STRING'])){
            $ch = curl_init("https://gw.open.1688.com/openapi/http/1/system.oauth2/getToken/{$this->appKey}?grant_type=authorization_code&need_refresh_token=true&client_id={$this->appKey}&client_secret={$this->secret}&redirect_uri={$this->redirectUri}&code=" . $_GET['code']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            try {
                $content = curl_exec($ch);
                $error  = curl_error($ch);
                if(!empty($error)){
                    throw new \Exception($error);
                }
                $tokenInfo = @json_decode($content, true);
                if(is_array($tokenInfo) && isset($tokenInfo['access_token'])){
                    $this->tokenInfo = $tokenInfo;
                }else{
                    $this->error = $content;
                }
            }catch (\Exception $e){
                $this->error = $e->getMessage();
            }
        }else{
            $url = "https://auth.1688.com/oauth/authorize?client_id={$this->appKey}&site=1688&redirect_uri={$this->redirectUri}&state={$this->state}";
            header("Location:{$url}");
            exit;
        }
    }

    /**
     * 获取凭证信息
     * @return array
     */
    public function tokenInfo(){
        return $this->tokenInfo;
    }

    /**
     * 获取临时凭证
     * @return string
     */
    public function getToken(){
        return $this->tokenInfo['access_token'];
    }

    /**
     * 获取长时凭证
     * @return string
     */
    public function getRefreshToken(){
        return $this->tokenInfo['refresh_token'];
    }

}