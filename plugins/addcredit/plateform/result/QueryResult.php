<?php

namespace app\plugins\addcredit\plateform\result;

class QueryResult
{
    const CODE_SUCC = 0; //查询成功
    const CODE_FAIL = 1; //查询失败

    public $code;
    public $message;

    //查询状态：waiting充值中 success已到账 fail失败
    public $status;

    public $response_content;

}