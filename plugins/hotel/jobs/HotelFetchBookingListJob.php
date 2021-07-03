<?php
namespace app\plugins\hotel\jobs;

use app\helpers\ArrayHelper;
use app\models\Mall;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\models\Hotels;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * 更新酒店可预定列表
 * @package app\plugins\hotel\jobs
 * @property int $mall_id
 * @property Hotels $hotel
 * @property string $start_date
 * @property int $days
 * @property string $cacheKey
 */
class HotelFetchBookingListJob extends BaseObject implements JobInterface{

    public $mall_id;
    public $hotel;
    public $start_date;
    public $days;
    public $cacheKey;

    public function execute($queue){

        \Yii::$app->mall = Mall::findOne($this->mall_id);

        $bookingListItems = [];
        $hotelPlateforms = $this->hotel->getPlateforms();
        foreach($hotelPlateforms as $hotelPlateform){
            $className = $hotelPlateform->plateform_class;
            if(empty($className) || !class_exists($className)) continue;
            $classObject = new $className();
            if(!$classObject instanceof IPlateform) continue;
            $result = $classObject->getBookingList($this->hotel, $hotelPlateform, $this->start_date, $this->days);
            if(!$result instanceof BookingListResult)
                continue;
            if($result->code != BookingListResult::CODE_SUCC)
                continue;
            $bookingListItems = array_merge($bookingListItems, $result->getAll());
        }
        $bookingList = [];
        foreach($bookingListItems as $bookingListItem){
            $arr = ArrayHelper::toArray($bookingListItem);
            $arr['product_thumb'] = !empty($arr['product_thumb']) ? $arr['product_thumb'] : $this->hotel->thumb_url;
            $bookingList[] = $arr;
        }

        //缓存2小时
        $cache = \Yii::$app->getCache();
        $cache->set($this->cacheKey, $bookingList, 3600 * 2);

        return $bookingList;
    }
}