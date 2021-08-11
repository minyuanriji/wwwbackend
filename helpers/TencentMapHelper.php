<?php
namespace app\helpers;

class TencentMapHelper{

    public static function toPoi($hostInfo, $lng, $lat){
        $cache = \Yii::$app->getCache();
        $cacheKey = "TencentMapHelper:toPoi:" . $lng . ":" . $lat;
        $cacheData = $cache->get($cacheKey);
        if(!$cacheData){
            $key = \Yii::$app->params['qqMapApiKey'];

            $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$lat.",".$lng."&key={$key}&get_poi=1";

            $ch = curl_init();
            //$hostInfo = "https://dev.mingyuanriji.cn";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_REFERER, $hostInfo);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $info = null;
            try {
                $result = @curl_exec($ch);

                $result = @json_decode($result);
                if(isset($result->status) && $result->status == 0){
                    $address_component = $result->result->address_component;
                    $info = [
                        'province' => $address_component->province,
                        'city' => $address_component->city,
                        'district' => $address_component->district
                    ];
                    if(!empty($address_component->street_number)){
                        $info['address'] = $address_component->street_number;
                    }else{
                        $info['address'] = $result->result->address;
                    }
                    $cache->set($cacheKey, $info, 3600 * 24 * 30);
                }
            }catch (\Exception $e){

            }

            @curl_close($ch);
        }else{
            $info = $cacheData;
        }

        return $info;
    }

    public static function toAddr($hostInfo, $address, $region = null){
        $cache = \Yii::$app->getCache();
        $cacheKey = "TencentMapHelper:toAddr:" . urlencode($region. $address);
        $cacheData = $cache->get($cacheKey);
        if(!$cacheData){
            $params[] = "address=" . urlencode($address);
            if(!empty($region)){
                $params[] = "region=" . $region;
            }
            $params[] = "key=" . \Yii::$app->params['qqMapApiKey'];

            $url = "https://apis.map.qq.com/ws/geocoder/v1/?" . implode("&", $params);

            $ch = curl_init();
            //$hostInfo = "https://dev.mingyuanriji.cn";
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_REFERER, $hostInfo);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $info = null;
            try {
                $result = @json_decode(@curl_exec($ch));
                if(isset($result->status) && $result->status == 0){
                    $address_component = $result->result->address_components;
                    $location = $result->result->location;
                    $info = [
                        'province'  => $address_component->province,
                        'city'      => $address_component->city,
                        'district'  => $address_component->district,
                        'longitude' => $location->lng,
                        'latitude'  => $location->lat
                    ];
                    $cache->set($cacheKey, $info, 3600 * 24 * 30);

                }
            }catch (\Exception $e){

            }

            @curl_close($ch);
        }else{
            $info = $cacheData;
        }

        return $info;
    }
}