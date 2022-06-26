<?php

namespace app\commands;

use app\models\User;
use app\plugins\smart_shop\components\SmartShopKPI;

class DebugController extends BaseCommandController{

    public function actionIndex(){
        echo \Yii::$app->getSecurity()->generatePasswordHash("gxp123456");
        exit;

    }
}