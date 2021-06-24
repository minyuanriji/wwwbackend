<?php
namespace app\helpers;

class TencentMapHelper{

    public static function toPoi($lng, $lat){
        $key = \Yii::$app->params['qqMapApiKey'];

        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$lat.",".$lng."&key={$key}&get_poi=1";
        $hostInfo = \Yii::$app->getRequest()->getHostInfo();
        $hostInfo = "http://dev.mingyuanriji.cn";

        $ch = curl_init();
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
            }
        }catch (\Exception $e){

        }

        @curl_close($ch);

        return $info;
    }

}