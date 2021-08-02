<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

class Code
{

    //1、充值请求状态码
        // 订单创建成功
        const ORDER_SUCCESS = 0;

    //2、查单状态码
        // 查询成功
        const QUERY_SUCCESS     = 5012;
        const QUERY_FAIL        = 5013;
        const QUERY_FREQUENTLY  = 5002;
        const QUERY_ORDER_EMPTY = 5005;

    //3、获取余额状态码
        //查询成功
        const BALANCE_QUERY_SUCCESS = 0;

        //操作频繁
        const Frequent_Operation = 5002;
}