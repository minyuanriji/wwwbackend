<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

class Config
{
    //下单请求
    const PHONE_BILL_SUBMIT = "/api/pay/telpay";

    //订单查询
    const ORDER_QUERY       = "/api/pay/telpay/query";

    //余额查询
    const BALANCE_QUERY     = "/api/pay/telpay/balance";


}