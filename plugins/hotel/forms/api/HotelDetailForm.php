<?php
namespace app\plugins\hotel\forms\api;


use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\helpers\ApiHotelHelper;
use app\plugins\hotel\models\Hotels;

class HotelDetailForm extends BaseModel{

    public $hotel_id;
    public $start_date;
    public $days;

    public function rules(){
        return [
            [['hotel_id', 'start_date', 'days'], 'required'],
            [['hotel_id', 'days'], 'integer', 'min' => 1]
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $todayStartTime = strtotime(date("Y-m-d") . " 00:00:00");
            $startTime = strtotime($this->start_date);

            if($startTime < $todayStartTime){
                throw new \Exception("起始日期不正确");
            }

            $hotel = Hotels::findOne($this->hotel_id);
            if(!$hotel || $hotel->is_delete){
                throw new \Exception("酒店不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'hotel_info'   => ApiHotelHelper::format($hotel),
                    'booking_list' => ApiHotelHelper::bookingList($hotel, $this->start_date, $this->days)
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