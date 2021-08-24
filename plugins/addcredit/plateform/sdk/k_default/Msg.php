<?php

namespace app\plugins\addcredit\plateform\sdk\k_default;

class Msg
{

    public static function submitMsg ()
    {
        return [
            101 => '参数缺失',
            102 => '商户不存在',
            103 => '商户已禁用',
            104 => '商户余额不足',
            105 => '订单超时时间错误',
            106 => '充值号码错误',
            107 => '运营商类型错误',
            108 => '签名校验失败',
            109 => '订单已存在',
            110 => '订单创建失败',
            111 => '号码状态异常',
            112 => '携号转网号码暂不支持',
            113 => '号码检测失效，请联系管理员',
            114  => '非授权ip',
        ];
    }

    public static function QueryMsg ()
    {
        return [
            101 => '参数缺失',
            102 => '商户不存在',
            103 => '订单号不存在',
            104 => '签名校验失败',
        ];
    }
}