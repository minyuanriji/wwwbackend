<?php

namespace app\plugins\smart_shop\controllers\api\kpi_admin;

use app\controllers\api\ApiController;
use app\plugins\smart_shop\components\SmartShop;

class AdminAuthController extends ApiController {

    public function beforeAction($action){
        $smartShop = new SmartShop();
        if(!isset($this->requestData['token']) || !$smartShop->validateToken($this->requestData['token'])){
            $this->error("无权限操作");
            exit;
        }
        return parent::beforeAction($action);
    }

}