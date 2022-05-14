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

    /**
     * 判断邮箱格式
     * @param $email
     * @return bool
     */
    public static function isEmail($email){
        $pattern = '#^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$#';
        return preg_match($pattern, $email) ? true : false;
    }
}