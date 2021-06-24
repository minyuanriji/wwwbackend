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

            $todayStartTime = strtotime(date("Y-m-d") . " 00:00:00");
            $startTime = strtotime($this->start_date);

            if($startTime < $todayStartTime){
                throw new \Exception("起始日期不正确");
            }

            //获取房型信息
            $room = HotelRoom::find()->where([
                "product_code" => $this->product_code,
                "is_delete" => 0
            ])->one();
            if(!$room){
                throw new \Exception("房型信息不存在");
            }

            //获取酒店信息
            $hotel = Hotels::findOne($room->hotel_id);
            if(!$hotel || $hotel->is_delete){
                throw new \Exception("酒店不存在");
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'hotel_info' => ApiHotelHelper::format($hotel),
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