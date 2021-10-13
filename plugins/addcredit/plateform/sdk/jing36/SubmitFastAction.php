<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

class SubmitFastAction{

    public $orderModel;
    public $plateModel;

    public function __construct(AddcreditOrder $addcreditOrder, AddcreditPlateforms $plateform){
        $this->orderModel = $addcreditOrder;
        $this->plateModel = $plateform;
    }

    public function run(){
        $configs = @json_decode($this->plateModel->json_param, true);
        $appKey = isset($configs['app_key']) ? $configs['app_key'] : "";
        $appSecret = isset($configs['app_secret']) ? $configs['app_secret'] : "";

        $req = new Req($appKey, $appSecret);
        $req->params['orderId']   = $this->orderModel->id;
        $req->params['mobile']    = $this->orderModel->mobile;
        $req->params['amount']    = $this->orderModel->order_price;
        $req->params['notifyUrl'] = "http://";
        $req->doPost();

    }


}