<?php

namespace app\plugins\addcredit\plateform\sdk\kcb_sdk;

class Helpers
{
    public static function getPlateConfig ($json_param)
    {
        $config['app_id'] = '';
        $config['secret_key'] = '';
        if (is_string($json_param)) {
            $params = @json_decode($json_param, true);
            foreach ($params as $item) {
                if (isset($item['name']) && $item['name']) {
                    if ($item['name'] == 'app_id' || $item['name'] == 'secret_key') {
                        $config[$item['name']] = $item['value'];
                    }
                }
            }
        }
        return $config;
    }
}