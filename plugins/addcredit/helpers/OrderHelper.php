<?php
namespace app\plugins\addcredit\helpers;

use app\helpers\CityHelper;
use app\helpers\MobileHelper;
use app\plugins\addcredit\models\AddcreditPlateforms;

class OrderHelper{

    public static function allow(AddcreditPlateforms $platModel, $mobile){

        //格式限制
        $denyList = !empty($platModel->pattern_deny) ? explode("\n", trim($platModel->pattern_deny)) : [];
        foreach($denyList as $deny){
            $deny = trim($deny);
            $pattern = "/^".str_replace("*", ".*", $deny)."$/";
            if(preg_match($pattern, $mobile)){
                throw new \Exception("抱歉！您的手机”{$mobile}“暂不支持充值");
            }
        }

        $mobieInfo = MobileHelper::getInfo($mobile);
        if(!in_array($mobieInfo['platCode'], [10000, 10086, 10010])){
            throw new \Exception("无法查询到手机运营商信息");
        }

        if(!in_array($mobieInfo['platCode'], explode(",", $platModel->allow_plats))){
            throw new \Exception("暂不支持".$mobieInfo['platName']."运营商手机号进行充值");
        }

        //区域限制
        $cityInfo = CityHelper::likeSearch($mobieInfo['province'], $mobieInfo['city']);
        $regionDenys = !empty($platModel->region_deny) ? @json_decode($platModel->region_deny, true) : [];
        foreach($regionDenys as $region){
            if(!empty($region['province_id']) && !empty($region['city_id']) && !empty($region['district_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id'] &&
                    $region['district_id'] == $cityInfo['district_id']){
                    throw new \Exception("暂不支持" . $region['province'] . $region['city'] . $region['district'] . "地区号码充值");
                }
            }elseif(!empty($region['province_id']) && !empty($region['city_id'])){
                if($region['province_id'] == $cityInfo['province_id'] &&
                    $region['city_id'] == $cityInfo['city_id']){
                    throw new \Exception("暂不支持" . $region['province'] . $region['city'] . "地区号码充值");
                }
            }elseif(!empty($region['province_id'])){
                if($region['province_id'] == $cityInfo['province_id']){
                    throw new \Exception("暂不支持" . $region['province'] . "地区号码充值");
                }
            }
        }

        return true;
    }

}