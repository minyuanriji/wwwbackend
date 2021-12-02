<?php

namespace app\plugins\addcredit\plateform\sdk\jing36;

use app\plugins\addcredit\models\AddcreditOrder;
use app\plugins\addcredit\models\AddcreditPlateforms;

abstract class BaseAction
{
    public $orderModel;
    public $plateModel;

    public function __construct(AddcreditOrder $addcreditOrder, AddcreditPlateforms $plateform){
        $this->orderModel = $addcreditOrder;
        $this->plateModel = $plateform;
    }

    abstract public function run();

    public function getPlateConfig ()
    {
        $paramArray = @json_decode($this->plateModel->json_param, true);
        $data = [];
        if($paramArray){
            foreach($paramArray as $item){
                $data[$item['name']] = $item['value'];
            }
        }
        $data['host']       = $data['host'] ?? '';
        $data['app_key']    = $data['app_key'] ?? '';
        $data['app_secret'] = $data['app_secret'] ?? '';
        return $data;
    }
}