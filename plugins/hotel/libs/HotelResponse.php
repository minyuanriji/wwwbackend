<?php
namespace app\plugins\hotel\libs;

use yii\base\BaseObject;

class HotelResponse extends BaseObject
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1;

    public $code;
    public $error;

    public $responseModel;
}