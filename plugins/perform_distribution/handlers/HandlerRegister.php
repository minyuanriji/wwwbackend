<?php

namespace app\plugins\perform_distribution\handlers;

use yii\base\BaseObject;

class HandlerRegister extends BaseObject
{
    public function getHandlers()
    {
        return [
            AwardOrderHandler::class
        ];
    }
}
