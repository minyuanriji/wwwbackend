<?php

namespace app\plugins\smart_shop\controllers\api\kpi_admin;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\components\SmartShop;

class AdminAuthController extends ApiController {

    protected $smartShop;
    protected $admin;
    protected $merchant;
    protected $store;

    public function beforeAction($action){
        $smartShop = new SmartShop();
        if(!isset($this->requestData['token']) || !$smartShop->validateToken($this->requestData['token'], $admin, $merchant, $store)){
            $this->error("无权限操作");
            exit;
        }
        $this->smartShop = $smartShop;
        $this->admin     = $admin;
        $this->merchant  = $merchant;
        $this->store     = $store;
        return parent::beforeAction($action);
    }

}