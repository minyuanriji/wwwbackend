<?php
namespace app\helpers;

class PoiHelper{

    /**
     * 判断是否是一个有效的地图坐标
     * @param $lng
     * @param $lat
     * @return bool
     */
    public static function isPoi($lng, $lat){
        if(!empty($lng) && !empty($lat)){
            $pattern = "/^\d+\.\d+$/i";
            if(preg_match($pattern, $lng) && preg_match($pattern, $lat)){
                return true;
            }
        }
        return false;
    }

}