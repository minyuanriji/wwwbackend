<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

class Config
{
    //获取accessToken
    const GET_ACCESS_TOKEN = "/produce/getaccesstoken";

    //获取商品详情
    const GET_GOODS_DETAIL = "/produce/getrechargedetail";

    //商品下单接口
    const CREATE_ORDER      = "/produce/createorder";

    //支付接口
    const PHONE_BILL_SUBMIT = "/produce/recharge";

    //订单查询
    const ORDER_QUERY       = "/produce/orderinfo";

    //查询用户信息
    const BALANCE_QUERY     = "user";


}