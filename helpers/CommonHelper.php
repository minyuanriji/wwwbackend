<?php
namespace app\helpers;


class CommonHelper{

    /**
     * 判断手机号格式
     * @param $mobile
     * @return bool
     */
    public static function isMobile($mobile){
        $pattern = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match($pattern, $mobile) ? true : false;
    }
}