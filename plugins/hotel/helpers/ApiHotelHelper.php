<?php
namespace app\plugins\hotel\helpers;

use app\controllers\api\ApiController;
use app\helpers\ArrayHelper;
use app\plugins\hotel\jobs\HotelFetchBookingListJob;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\models\Hotels;

class ApiHotelHelper{

    /**
     * 输出酒店信息
     * @param Hotels $hotel
     * @return array
     */
    public static function format(Hotels $hotel){
        $info = $hotel->getAttributes();

        //标签
        $info['tag'] = !empty($info['tag']) ? explode(",", $info['tag']) : [];

        //类型
        $typeTexts = ['luxe' => '豪华型', 'comfort' => '舒适型', 'eco' => '经济型'];
        $info['type_text'] = $typeTexts[$info['type']];

        //距离
        $info['distance'] = static::getDistance($hotel);

        return $info;
    }
    
    public static function getDistance(Hotels $hotel){

        //经度1
        $lng1 = isset(ApiController::$commonData['city_data']['longitude']) ? ApiController::$commonData['city_data']['longitude'] : null;
        //纬度1
        $lat1 = isset(ApiController::$commonData['city_data']['latitude']) ? ApiController::$commonData['city_data']['latitude'] : null;

        $lng2 = $hotel->tx_lng; //经度2
        $lat2 = $hotel->tx_lat;  //纬度2

        $distance = ['di' => -1, 'unit' => 'm'];
        if(empty($lng1) || empty($lat1) || empty($lng2) || empty($lat2)){
            return $distance;
        }else{
            $EARTH_RADIUS = 6378137;   //地球半径
            $RAD = pi() / 180.0;

            $radLat1 = $lat1 * $RAD;
            $radLat2 = $lat2 * $RAD;
            $a = $radLat1 - $radLat2;    // 两点纬度差
            $b = ($lng1 - $lng2) * $RAD;  // 两点经度差
            $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));

            $long = ((($s * $EARTH_RADIUS) * 10000) / 10000);
            if($long > 1000){
                $distance['di'] = round($long/1000, 1);
                $distance['unit'] = "km";
            }else{
                $distance['di'] = $long;
            }

            return $distance;
        }

    }

    /**
     * 获取酒店客预定列表
     * @param Hotels $hotel
     * @param $start_date
     * @param $days
     * @return array
     */
    public static function bookingList(Hotels $hotel, $start_date, $days){
        $cache = \Yii::$app->getCache();
        $cacheKey = "HotelBookingList:" . $hotel->id . ":{$start_date}:{$days}";


        $job = new HotelFetchBookingListJob([
            "mall_id"    => \Yii::$app->mall->id,
            "hotel"      => $hotel,
            "start_date" => $start_date,
            "days"       => $days,
            "cacheKey"   => $cacheKey
        ]);

        $bookingList = $cache->get($cacheKey);

        if( (defined('ENV') && ENV != "pro") || !$bookingList){
            $bookingList = $job->execute(null);
        }else{
            \Yii::$app->queue->delay(0)->push($job);
        }

        return $bookingList ? $bookingList : [];
    }
}