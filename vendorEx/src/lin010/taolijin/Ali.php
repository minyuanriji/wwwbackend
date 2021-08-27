<?php

namespace lin010\taolijin;


use lin010\taolijin\ali\taobao\tbk\material\Material;

class Ali{

    private $client;

    public $material;

    public function __construct($key, $secret){
        include_once __DIR__ . '/ali/sdk/TopSdk.php';
        $this->client = new \TopClient();
        $this->client->appkey    = $key;
        $this->client->secretKey = $secret;

        $this->material = new Material($this);
    }

    public function getClient(){
        return $this->client;
    }
}