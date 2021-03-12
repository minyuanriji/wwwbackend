<?php
namespace app\mch\handlers;

use app\handlers\BaseHandler;
use app\plugins\mch\models\MchCheckoutOrder;

class MchModuleHandler extends BaseHandler
{
    /**
     * 事件处理注册
     */
    public function register()
    {
        \Yii::$app->on(MchCheckoutOrder::EVENT_PAYED, [CheckoutOrderPaidHandler::class, 'handle']);
    }
}
