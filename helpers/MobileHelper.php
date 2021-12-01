<?php

namespace app\helpers;

use lin010\mobile\plat\tx_cloud_15700\TenCloud15700;
use lin010\mobile\QueryMobileInfoResult;

class MobileHelper
{

    /**
     * 查询手机归属地信息
     * @param $mobile
     * @param false $refresh
     * @return array
     */
    public static function getInfo($mobile, $refresh = false){

        $info     = ["province" => "", "city" => "", "platName" => "", "platCode" => ""];
        $cacheObj = \Yii::$app->getCache();
        $cacheKey = "MobileHelper::getInfo:{$mobile}";

        $cacheData = $cacheObj->get($cacheKey);
        if(!$refresh && $cacheData && isset($cacheData['platCode']) && $cacheData['platCode']){
            return $cacheData;
        }
        $mobileService = new TenCloud15700();
        $queryResult = $mobileService->queryMobileInfo($mobile);
        if($queryResult instanceof QueryMobileInfoResult){
            if($queryResult->code == QueryMobileInfoResult::CODE_SUCC){
                $info['province'] = $queryResult->province;
                $info['city']     = $queryResult->city;
                $info['platName'] = $queryResult->platName;
                $info['platCode'] = $queryResult->platCode;
                $cacheObj->set($cacheKey, $info);
            }
        }
        return $info;
    }

    /**
     * 判断是否手机号码
     * @param $mobile
     * @return boolean
     */
    public static function isMobile($mobile){
        return preg_match("/^1[3456789]\d{9}$/", $mobile);
    }
}