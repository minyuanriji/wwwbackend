<?php
namespace app\plugins\hotel\forms\api;


use app\core\ApiCode;
use app\helpers\ArrayHelper;
use app\models\BaseModel;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\models\Hotels;

class HotelDetailForm extends BaseModel{

    public $hotel_id;
    public $start_date;
    public $days;

    public function rules(){
        return [
            [['hotel_id', 'start_date', 'days'], 'required'],
            [['hotel_id', 'days'], 'integer']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {
            $hotel = Hotels::findOne($this->hotel_id);
            if(!$hotel || $hotel->is_delete){
                throw new \Exception("酒店不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'hotel_info'  => $this->hotelInfo($hotel),
                    'booking_list' => $this->bookingList($hotel)
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 查询合作平台可预订房间
     * @param Hotels $hotel
     * @throws \app\plugins\hotel\libs\HotelException
     * @return array
     */
    private function bookingList(Hotels $hotel){
        $bookingListItems = [];
        $hotelPlateforms = $hotel->getPlateforms();
        foreach($hotelPlateforms as $hotelPlateform){
            $className = $hotelPlateform->plateform_class;
            if(empty($className) || !class_exists($className)) continue;
            $classObject = new $className();
            if(!$classObject instanceof IPlateform) continue;
            $result = $classObject->getBookingList($hotel, $hotelPlateform, $this->start_date, $this->days);
            if(!$result instanceof BookingListResult)
                continue;
            if($result->code != BookingListResult::CODE_SUCC)
                continue;
            $bookingListItems = array_merge($bookingListItems, $result->getAll());
        }
        $bookingList = [];
        foreach($bookingListItems as $bookingListItem){
            $arr = ArrayHelper::toArray($bookingListItem);
            $arr['product_thumb'] = !empty($arr['product_thumb']) ? $arr['product_thumb'] : $hotel->thumb_url;
            $bookingList[] = $arr;
        }

        return $bookingList;
    }

    private function hotelInfo(Hotels $hotel){
        $info = $hotel->getAttributes();

        //标签
        $info['tag'] = !empty($info['tag']) ? explode(",", $info['tag']) : [];

        //类型
        $typeTexts = ['luxe' => '豪华型', 'comfort' => '舒适型', 'eco' => '经济型'];
        $info['type_text'] = $typeTexts[$info['type']];

        return $info;
    }

}