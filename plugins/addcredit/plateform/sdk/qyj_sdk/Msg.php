<?php

namespace app\plugins\addcredit\plateform\sdk\qyj_sdk;

class Msg
{

    public static function submitMsg ()
    {
        return [
            30150 => '无效供应商或已删除，请检查',
            40001 => '无效商户或不存在，请检查',
            40018 => '授信值不足，请充值',
            40155 => '签名校验失败，请检查',
            40250 => '该账单已存在，请勿重复添加！',
        ];
    }

    public static function QueryMsg ()
    {
        return [
            40155 => '签名校验失败，请检查',
            40001 => '无效商户或不存在，请检查',
            40290 => '该账单未创建或已过期，请检查',
        ];
    }
}