<?php
namespace app\plugins\hotel\forms\api\order;

use app\core\ApiCode;
use app\models\BaseModel;
use app\plugins\hotel\helpers\ApiHotelHelper;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;

class HotelOrderPreviewForm extends BaseModel{

    public $unique_id;
    public $product_code;
    public $start_date;
    public $days;

    public function rules(){
        return [
            [['unique_id', 'product_code', 'start_date', 'days'], 'required'],
            [['days'], 'integer', 'min' => 1]
        ];
    }

    public function preview(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        try {

            $this->validateStartDate();

            //获取房型信息
            $room = $this->getRoom();

            //获取酒店信息
            $hotel = $this->getHotel($room);

            $bookingItem = $this->getBookingItem($hotel);

            $endDay = date("Y-m-d", strtotime($this->start_date) + $this->days * 3600 * 24);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'start_day'    => $this->start_date,
                    'end_day'      => $endDay,
                    'days'         => (int)$this->days,
                    'hotel_info'   => ApiHotelHelper::format($hotel),
                    'booking_item' => $bookingItem
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
     * 检查起始日期
     * @throws \Exception
     */
    protected function validateStartDate(){
        $todayStartTime = strtotime(date("Y-m-d") . " 00:00:00");
        $startTime = strtotime($this->start_date);

        if($startTime < $todayStartTime){
            throw new \Exception("起始日期不正确");
        }

        $this->start_date = date("Y-m-d", $startTime);
    }

    /**
     * 获取房型信息
     * @return array|\yii\db\ActiveRecord
     * @throws \Exception
     */
    protected function getRoom(){
        $room = HotelRoom::find()->where([
            "product_code" => $this->product_code,
            "is_delete"    => 0
        ])->one();
        if(!$room){
            throw new \Exception("房型信息不存在");
        }
        return $room;
    }

    /**
     * 获取酒店信息
     * @return Hotels
     * @throws \Exception
     */
    protected function getHotel(HotelRoom $room){
        $hotel = Hotels::findOne($room->hotel_id);
        if(!$hotel || $hotel->is_delete){
            throw new \Exception("酒店不存在");
        }
        return $hotel;
    }

    /**
     * 获取产品信息
     * @param Hotels $hotel
     * @return mixed
     * @throws \Exception
     */
    protected function getBookingItem(Hotels $hotel){
        $bookingList = ApiHotelHelper::bookingList($hotel, $this->start_date, $this->days);
        $bookingItem = null;
        foreach($bookingList as $item){
            if($item['unique_id'] == $this->unique_id){
                $bookingItem = $item;
                break;
            }
        }
        if(!$bookingItem){
            throw new \Exception("无法查询到酒店预订信息");
        }
        return $bookingItem;
    }
}