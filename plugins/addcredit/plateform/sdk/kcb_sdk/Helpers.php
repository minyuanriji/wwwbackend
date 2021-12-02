<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

class Helpers
{
    public static function getPlateConfig ($json_param)
    {
        $paramArray = @json_decode($json_param, true);
        $data = [];
        if($paramArray){
            foreach($paramArray as $item){
                $data[$item['name']] = $item['value'];
            }
        }
        $data['app_id']         = $data['app_id'] ?? '';
        $data['secret_key']     = $data['secret_key'] ?? '';
        return $data;
    }
}