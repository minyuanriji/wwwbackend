<?php
namespace app\plugins\hotel\helpers;

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

        return $info;
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