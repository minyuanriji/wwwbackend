<?php

namespace app\helpers;

class CloudStorageHelper{

    /**
     * 云存储域名替换成自定义域名
     * @param $url
     * @return array|string|string[]|null
     */
    public static function url($url){
        $hostName = \Yii::$app->params['tencentCloud']['host_name'];
        $url = preg_replace("/https?:\/\/[^\/]+\//i", rtrim($hostName, "/") . "/", $url);
        return $url;
    }
}