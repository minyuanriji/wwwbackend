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

    /**
     * 通过区、市、省ID获取区、市、省数据
     * @param int $district_id
     * @param int $city_id
     * @param int $province_id
     * @return array|null
     */
    public static function reverseData($district_id = 0, $city_id = 0, $province_id = 0){
        static $arrs = null;
        if($arrs == null){
            $arrs = DistrictData::getArr();
        }
        $district = $city = $province = null;
        if(isset($arrs[$district_id])){
            $district = $arrs[$district_id];
            if(isset($arrs[$district['parent_id']])){
                $city = $arrs[$district['parent_id']];
                if(isset($arrs[$city['parent_id']])){
                    $province = $arrs[$city['parent_id']];
                }
            }
        }elseif(isset($arrs[$city_id])){
            $city = $arrs[$city_id];
            if(isset($arrs[$city['parent_id']])){
                $province = $arrs[$city['parent_id']];
            }
        }elseif(isset($arrs[$province_id])){
            $province = $arrs[$province_id];
        }

        return [
            'province' => $province,
            'city'     => $city,
            'district' => $district
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

    /*
     * 通过经纬度获取城市信息
     * */
    public static function getCityInfo ($latitude, $longitude)
    {
        if (empty($latitude) || empty($longitude))
            return false;

        $key = \Yii::$app->params['qqMapApiKey'];
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$latitude.",".$longitude."&key={$key}&get_poi=1";
        $hostInfo = "https://www.mingyuanriji.cn";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $hostInfo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = @curl_exec($ch);
        @curl_close($ch);

        $data = !empty($result) ? json_decode($result, true) : null;

        return $data;
    }

    /**
     * 通过市名获取下级县
     * @param $name2 市名
     */
    public static function getDistrictName($cityName)
    {
        if (empty($cityName))
            return false;

        $data = [];
        $arrs = DistrictData::getArr();
        foreach($arrs as $arr){
            if($arr['level'] == "city" && $arr['name'] == $cityName){
                $city_id = $arr['id'];
            }
        }
        if ($city_id > 0) {
            foreach($arrs as $arr){
                if($arr['parent_id'] == $city_id){
                    $data[] = $arr['name'];
                }
            }
        }
        return ['city_id' => $city_id, 'district' => $data];
    }

}