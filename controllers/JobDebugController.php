<?php
namespace app\controllers;

use app\models\Order;
use app\plugins\perform_distribution\events\AwardOrderEvent;

class JobDebugController extends BaseController {

    public function actionIndex(){
        \Yii::$app->trigger(AwardOrderEvent::SHOP_ORDER_PAID,
            new AwardOrderEvent([
                'order' => Order::findOne(19231)
            ])
        );
    }
}
