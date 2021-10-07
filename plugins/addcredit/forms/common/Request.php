<?php

namespace app\plugins\addcredit\forms\common;

use app\core\ApiCode;

class Request
{

    private static $devUrl = "http://weixin.kuaichongbei.com/yrapi.php/index/";

    public static function execute($url, $params)
    {
        try {
            $headers = [
                'Content-Type：application/x-www-form-urlencoded'
            ];
            $ch = curl_init(static::$devUrl . $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
        $headers = [
            'Content-Type：application/x-www-form-urlencoded'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, static::$devUrl . $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $data = curl_exec($ch);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        echo curl_error($ch);
        if (curl_errno($ch) > 0) {
            echo curl_error($ch);
        }
        curl_close($ch);
        return $data;
    }
}