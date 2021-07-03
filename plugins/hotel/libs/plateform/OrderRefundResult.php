<?php
namespace app\plugins\hotel\libs\plateform;


class OrderRefundResult
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1;

    public $code = 0;
    public $message;

    public $originData = null;
}