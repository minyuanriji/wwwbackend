<?php
/*
 * xuyaoxiang
 * 2020/09/03
 */

namespace app\plugins\group_buy\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            OrderCreatedHandler::class,
            OrderPayedHandler::class,
            OrderConfirmedHandler::class,
            OrderCanceledHandler::class,
            OrderRefundConfirmedHandler::class,
            GroupBuySuccessHandler::class,
            GroupBuyFailedHandler::class
        ];
    }
}