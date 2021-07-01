<?php
namespace app\plugins\hotel\libs\bestwehotel;

use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;

class Request{

    private static $devUrl = "https://bizfzout.bestwehotel.com/proxy/ms-corp-directly-connect/";
    private static $proUrl = "https://bizfzout.bestwehotel.com/proxy/ms-corp-directly-connect/";

    public static function execute(BaseClient $client){

        $result = null;
        try {

            $timestamp = time() * 1000;
            $sign = hash("sha256", Config::getAppId() . "-" . $timestamp . "-" . Config::getKey());
            $dataJsonString = $client->getDataJSONString();
            $headers = [
                'fizz-appid: ' . Config::getAppId(),
                'timestamp: '. $timestamp,
                'sign: ' . $sign,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataJsonString)
            ];

            $ch = curl_init(static::$devUrl . $client->getUri());
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJsonString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            $result = curl_exec($ch);
            $errno  = curl_errno($ch);
            $error  = curl_error($ch);

            if(!empty($error)){
                throw new HotelException($error);
            }

            return new HotelResponse([
                'code'          => HotelResponse::CODE_SUCC,
                'responseModel' => $client->parseResult($result)
            ]);
        }catch (HotelException $e){
            return new HotelResponse([
                'code'           => HotelResponse::CODE_FAIL,
                'error'          => $e->getMessage(),
                'result_content' => $result
            ]);
        }

    }

}