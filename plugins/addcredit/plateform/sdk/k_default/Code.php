<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

class Code
{

    //1、充值请求状态码
        // 订单创建成功
        const ORDER_SUCCESS = 0;

    //2、查单状态码
        // 查询成功
        const QUERY_SUCCESS     = 100;

        //1 未支付 2 支付中 3 已支付 4 支付失败
        const PAY_STATUS_UNPAID = 1;
        const PAY_STATUS_PAYMENT = 2;
        const PAY_STATUS_PAID = 3;
        const PAY_STATUS_FAIL = 4;

        //0未到账 1到账中 2已到账 3已退款
        const COMPLETE_STATUS_NON_ARRIVAL = 0;
        const COMPLETE_STATUS_TO_ACCOUNT = 1;
        const COMPLETE_STATUS_RECEIVED = 2;
        const COMPLETE_STATUS_REFUNDED = 3;



    //3、获取余额状态码
        //查询成功
        const BALANCE_QUERY_SUCCESS = 0;

        //操作频繁
        const Frequent_Operation = 5002;
}