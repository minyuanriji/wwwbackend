<?php

namespace app\commands;

use app\models\User;
use app\plugins\smart_shop\components\SmartShopKPI;

class DebugController extends BaseCommandController{

    public function actionIndex(){
        $kpi = new SmartShopKPI();
        $kpi->register(
            User::findOne(182),
            User::findOne(6955),
            2, 2);

    }
}