<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

class Req{

    public $appKey;
    public $appSecret;
    public $params = [];

    public function __construct($appKey, $appSecret) {
        $this->appKey    = $appKey;
        $this->appSecret = $appSecret;
    }

    public function doPost(){

        try {

        }catch (\Exception $e){

        }

    }

    public static function getSign(&$params){

    }
}