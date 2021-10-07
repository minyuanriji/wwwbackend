<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

class Config
{
    //下单请求
    const PHONE_BILL_SUBMIT = "recharge";

    //订单查询
    const ORDER_QUERY       = "check";

    //查询用户信息
    const BALANCE_QUERY     = "user";

    //快充产品ID
    const FAST_CHARGING      = [10, 28, 123, 124, 125, 126];

    //慢充产品ID
    const SLOW_CHARGE        = [83, 84, 85, 86];


}