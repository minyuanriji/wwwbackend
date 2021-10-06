<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

class Code
{
    //1、全局成功状态码
        const OVERALL_SITUATION_SUCCESS = 200;

    //2、支付结果状态码
        const PAY_STATUS_SUCCESS = 1;

    //3、获取余额状态码
        //查询成功
        const BALANCE_QUERY_SUCCESS = 0;

        //操作频繁
        const Frequent_Operation = 5002;
}