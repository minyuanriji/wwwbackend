<?php

namespace app\commands;

use app\models\User;
use app\plugins\smart_shop\components\SmartShop;
use app\plugins\smart_shop\components\SmartShopKPI;
use app\plugins\smart_shop\models\Cyorder;

class DebugController extends BaseCommandController{

    public function actionIndex(){
        $orderIds = Cyorder::find()->select(["id"])->andWhere([
            "AND",
            ["status" => 0],
            ["<", "created_at", time() - 20],
        ])->orderBy("updated_at ASC")->limit(1)->column();
        print_r($orderIds);
        exit;
    }
}