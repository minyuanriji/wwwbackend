<?php

namespace lin010\taolijin;


use lin010\taolijin\ali\taobao\tbk\auth\Auth;
use lin010\taolijin\ali\taobao\tbk\cat\Cat;
use lin010\taolijin\ali\taobao\tbk\invitecode\Invitecode;
use lin010\taolijin\ali\taobao\tbk\item\Item;
use lin010\taolijin\ali\taobao\tbk\material\Material;
use lin010\taolijin\ali\taobao\tbk\order\Order;
use lin010\taolijin\ali\taobao\tbk\publisher\Publisher;
use lin010\taolijin\ali\taobao\tbk\spread\Spread;
use lin010\taolijin\ali\taobao\tbk\tlj\Tlj;

class Ali{

    private $client;

    public $material; //物料

    public $tlj; //淘礼金

    public $spread; //推广

    public $item; //公用

    public $order; //订单

    public $auth; //授权

    public $invitecode; //邀请码

    public $cat; //类目

    public $publisher;

    public function __construct($key, $secret){
        include_once __DIR__ . '/ali/sdk/TopSdk.php';
        $this->client = new \TopClient();
        $this->client->appkey    = $key;
        $this->client->secretKey = $secret;

        $this->material   = new Material($this);
        $this->tlj        = new Tlj($this);
        $this->spread     = new Spread($this);
        $this->item       = new Item($this);
        $this->order      = new Order($this);
        $this->auth       = new Auth($this);
        $this->invitecode = new Invitecode($this);
        $this->cat        = new Cat($this);
        $this->publisher  = new Publisher($this);
    }

    public function getClient(){
        return $this->client;
    }
}