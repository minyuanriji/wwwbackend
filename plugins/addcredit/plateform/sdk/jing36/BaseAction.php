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
        $config['app_key'] = '';
        $config['app_secret'] = '';
        $config['host'] = '';
        if (is_string($this->plateModel->json_param)) {
            $params = @json_decode($this->plateModel->json_param, true);
            foreach ($params as $item) {
                if (isset($item['name']) && $item['name']) {
                    if ($item['name'] == 'app_key' || $item['name'] == 'app_secret' || $item['name'] == 'host') {
                        $config[$item['name']] = $item['value'];
                    }
                }
            }
        }
        return $config;
    }
}