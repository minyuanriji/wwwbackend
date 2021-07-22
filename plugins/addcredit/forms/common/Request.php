<?php

namespace app\plugins\addcredit\forms\common;

use app\core\ApiCode;

class Request
{

    private static $devUrl = "";

    public static function execute($url, $post_param)
    {
        try {
            $ch = curl_init(static::$devUrl . $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);

            if (!empty($error)) {
                throw new \Exception($error, ApiCode::CODE_FAIL);
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'code'  => ApiCode::CODE_FAIL,
                'msg'   => $e->getMessage(),
            ];
        }
    }
}