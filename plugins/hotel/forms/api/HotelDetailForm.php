<?php
namespace app\plugins\hotel\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelRoomStatusClient;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelRoomStatusRequest;
use app\plugins\hotel\libs\plateform\BookingListItemModel;
use app\plugins\hotel\libs\plateform\BookingListResult;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\IPlateform;
use app\plugins\hotel\models\Hotels;
use app\plugins\hotel\libs\bestwehotel\Request;

class HotelDetailForm extends BaseModel{

    public $hotel_id;
    public $start_date;
    public $days;

    public function rules(){
        return [
            [['hotel_id', 'start_date', 'days'], 'required']
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

            //查询合作平台可预订房间
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

            print_r($bookingListItems);
            exit;

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'hotel_info' => $hotel->getAttributes()
                ]
            ];
        }catch (\Exception $e){
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

}