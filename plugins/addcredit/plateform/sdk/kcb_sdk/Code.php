<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

class Code
{

    //1、充值请求状态码
        // 订单创建成功
        const ORDER_SUCCESS = 0;

    //2、查单状态码
        // 查询成功
        const QUERY_RECHARGING = 0;
        const QUERY_SUCCESS    = 1;
        const QUERY_FAIL       = 2;

    //3、获取余额状态码
        //查询成功
        const BALANCE_QUERY_SUCCESS = 0;

        //操作频繁
        const Frequent_Operation = 5002;
}