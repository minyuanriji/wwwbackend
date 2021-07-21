<?php

namespace app\plugins\addcredit\plateform\result;

class BalanceQueryResult
{
    const CODE_SUCC = 0; //提交成功
    const CODE_FAIL = 1; //提交失败

    public $code;
    public $message;

    public $balance; //余额
}