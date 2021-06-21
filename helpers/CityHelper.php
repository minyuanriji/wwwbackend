<?php
namespace app\helpers;

use app\models\DistrictData;

class CityHelper{

    private static $province_id;
    private static $city_id;
    private static $district_id;
    private static $province_name;
    private static $city_name;
    private static $district_name;

    private static $provinceArr = [];
    private static $cityArr = [];
    private static $districtArr = [];

    /**
     * 模糊搜索省/市/区
     * @param $name1 省名
     * @param $name2 市名
     * @param $name3 区名
     */
    public static function likeSearch($name1, $name2 = null, $name3 = null){
        static::reset();

        $arrs = DistrictData::getArr();
        foreach($arrs as $arr){
            if($arr['level'] == "province"){
                static::$provinceArr[$arr['id']] = $arr;
            }
        }

        static::setProvince($name1);
        static::setCity($name2);
        static::setDistrict($name3);

        return [
            'province_id'   => static::$province_id,
            'province_name' => static::$province_name,
            'city_id'       => static::$city_id,
            'city_name'     => static::$city_name,
            'district_id'   => static::$district_id,
            'district_name' => static::$district_name,
        ];
    }

    private static function setProvince($name){

        if(empty($name))
            return;

        $name = preg_replace("/省$/", "", trim($name));
        $arrs = DistrictData::getArr();
        foreach(static::$provinceArr as $province){
            if(strstr($province['name'], $name) !== false){
                static::$province_id   = $province['id'];
                static::$province_name = $province['name'];
                foreach($arrs as $arr){
                    if($arr['level'] == "city" && $arr['parent_id'] == $province['id']){
                        static::$cityArr[] = $arr;
                    }
                }
                break;
            }
        }
    }

    private static function setCity($name){
        if(empty($name) || empty(static::$province_id))
            return;
        $name = preg_replace("/市$/", "", trim($name));
        $arrs = DistrictData::getArr();
        foreach(static::$cityArr as $city){
            if(strstr($city['name'], $name) !== false){
                static::$city_id   = $city['id'];
                static::$city_name = $city['name'];
                foreach($arrs as $arr){
                    if($arr['level'] == "district" && $arr['parent_id'] == $city['id']){
                        static::$districtArr[] = $arr;
                    }
                }
                break;
            }
        }
    }

    private static function setDistrict($name){
        if(empty($name) || empty(static::$city_id))
            return;
        foreach(static::$districtArr as $district){
            if(strstr($district['name'], $name) !== false){
                static::$district_id   = $district['id'];
                static::$district_name = $district['name'];
            }
        }
    }

    private static function reset(){
        static::$cityArr       = [];
        static::$provinceArr   = [];
        static::$districtArr   = [];
        static::$city_id       = 0;
        static::$city_name     = "";
        static::$district_id   = 0;
        static::$district_name = "";
        static::$province_id   = 0;
        static::$province_name = "";
    }

}