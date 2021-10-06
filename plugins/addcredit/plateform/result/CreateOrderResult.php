<?php

namespace app\plugins\addcredit\plateform\result;

class CreateOrderResult
{
    const CODE_SUCC = 0; //提交成功
    const CODE_FAIL = 1; //提交失败

    public $code;
    public $message;

    public $request_data; //请求数据
    public $response_content; //返回的原始数据
}