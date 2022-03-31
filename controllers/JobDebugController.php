<?php
namespace app\controllers;


use app\logic\IntegralLogic;
use app\models\Mall;
use app\models\Order;
use app\models\Wechat;

class JobDebugController extends BaseController {

    public function actionIndex(){

        \Yii::$app->mall = Mall::findOne(5);

        $order = Order::findOne(25274);

        $integralLogic = new IntegralLogic();
        $integralLogic->refundIntegral($order,0);
        //$integralLogic->refundIntegral($order,1);
    }
}
