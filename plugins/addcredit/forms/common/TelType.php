<?php

namespace app\plugins\addcredit\forms\common;

class TelType
{
    public function getPhoneType ($phone)
    {
        $phone = trim($phone);
        $isChinaMobile = "/^134[0-8]\d{7}$|^(?:13[5-9]|147|15[0-27-9]|178|18[2-478])\d{8}$/"; //移动方面最新答复
        $isChinaUnion = "/^(?:13[0-2]|145|15[56]|176|18[56])\d{8}$/"; //向联通微博确认并未回复
        $isChinaTelcom = "/^(?:133|153|177|173|18[019])\d{8}$/"; //1349号段 电信方面没给出答复，视作不存在
        // $isOtherTelphone = "/^170([059])\\d{7}$/";//其他运营商
        if (preg_match($isChinaMobile, $phone)) {
            return 1;
        } elseif (preg_match($isChinaUnion, $phone)) {
            return 2;
        } elseif (preg_match($isChinaTelcom, $phone)) {
            return 3;
        } else {
            return 0;
        }
    }
}