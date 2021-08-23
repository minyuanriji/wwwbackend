<?php

namespace app\plugins\area\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            AreaCommonOrderDetailHandler::class,
            CommonOrderPayedHandler::class
        ];
    }
}
