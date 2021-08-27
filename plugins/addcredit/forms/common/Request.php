<?php

namespace app\plugins\addcredit\forms\common;

use app\core\ApiCode;

class Request
{

    private static $devUrl = "http://94.74.98.124:9999";

    public static function execute($url, $post_param)
    {
        try {
            $headers = [
                'Content-Type: application/json'
            ];
            $ch = curl_init(static::$devUrl . $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);

            if (!empty($error)) {
                throw new \Exception($error);
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'code'  => ApiCode::CODE_FAIL,
                'msg'   => $e->getMessage(),
            ];
        }
    }

    public static function http_get($url)
    {
        //1. 初始化
        $ch = curl_init();
        //1.2 设置请求的url地址
        curl_setopt($ch, CURLOPT_URL, static::$devUrl . $url);
        //1.6 请求头关闭
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //1.5 请求得到的结果不直接输出，而是以字符串结果返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //1.7 设置请求超时时间,单位为秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //1.8 设置浏览器型号,可要可不要
        //    curl_setopt($ch,CURLOPT_USERAGENT,'MSIE001');
        //1.3发送请求
        $data = curl_exec($ch);

        //2.1 证书不检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //1.9 获取是否有异常,大于0就是有异常
        echo curl_error($ch);
        if (curl_errno($ch) > 0) {
            //2. 输出错误信息
            echo curl_error($ch);
        };

        //1.4 关闭请求
        curl_close($ch);
        return $data;
    }
}