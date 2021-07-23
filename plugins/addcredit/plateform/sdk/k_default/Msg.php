<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

class Msg
{

    public static function msg ()
    {
        return [
            1000 => '系统接口维护',
            1001 => 'szTimeStamp参数时间格式错误，正确格式：yyyy-MM-dd HH:mm:ss(例如：2016-01-01 07:23:00)',
            1003 => 'szTimeStamp时间参数与我方收到订单的时间晚于1分钟以上',
            1004 => '签名错误',
            1006 => '平台余额不足，请稍后再试！',
            2001 => '号段错误',
            2002 => '运营商错误',
            2003 => '号码黑名单',
            2020 => 'IP白名单限制',
            2021 => '用户不存在',
            2030 => '产品没有配置或关闭',
            3003 => '订单已存在',
            2050 => '下单异常',
            999  => '系统异常',
        ];
    }
}