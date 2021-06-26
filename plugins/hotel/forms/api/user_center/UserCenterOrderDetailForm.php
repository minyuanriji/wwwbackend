<?php
namespace app\plugins\hotel\forms\api\user_center;


use app\core\ApiCode;
use app\models\BaseModel;
use app\models\DistrictData;
use app\plugins\hotel\helpers\OrderHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelRoom;
use app\plugins\hotel\models\Hotels;

class UserCenterOrderDetailForm extends BaseModel {

    public $hotel_order_id;

    public function rules(){
        return [
            [['hotel_order_id'], 'required']
        ];
    }

    public function getDetail(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }
        try {

            //获取订单详情
            $orderDetail = HotelOrder::find()->asArray()->where(["id" => $this->hotel_order_id])->one();
            if(!$orderDetail){
                throw new \Exception("订单不存在");
            }

            $statusInfo = OrderHelper::getOrderRealStatus($orderDetail['order_status'], $orderDetail['pay_status'], $orderDetail['created_at'], $orderDetail['booking_start_date'], $orderDetail['booking_days']);
            $orderDetail['status_text'] = $statusInfo['text'];
            $orderDetail['real_status'] = $statusInfo['status'];
            $orderDetail['created_at'] = date("Y-m-d H:i", $orderDetail['created_at']);
            $orderDetail['updated_at'] = date("Y-m-d H:i", $orderDetail['updated_at']);
            $orderDetail['booking_passengers'] = @json_decode($orderDetail['booking_passengers'], true);
            $orderDetail['origin_booking_data'] = @json_decode($orderDetail['origin_booking_data'], true);

            //酒店信息
            $selects = [
                "thumb_url", "name", "type", "tx_lat", "tx_lng", "is_open", "is_booking",
                "open_time", "building_time", "descript", "price", "tag", "cmt_grade",
                "cmt_text1", "cmt_text2", "cmt_num", "contact_phone", "contact_mobile",
                "address", "near_subway", "policy_into_time", "policy_out_time",
                "policy_add_bed", "policy_pets", "policy_breakfast", "json_service_facilitys",
                "province_id", "city_id"
            ];
            $hotelDetail = Hotels::find()->asArray()->where(["id" => $orderDetail['hotel_id']])->one();
            if(!$hotelDetail){
                throw new \Exception("酒店不存在");
            }

            $districtArr = DistrictData::getArr();
            $hotelDetail['province_name'] = $hotelDetail['city_name'] = "";
            if(isset($districtArr[$hotelDetail['province_id']])){
                $hotelDetail['province_name'] = $districtArr[$hotelDetail['province_id']]['name'];
            }
            if(isset($districtArr[$hotelDetail['city_id']])){
                $hotelDetail['city_name'] = $districtArr[$hotelDetail['city_id']]['name'];
            }

            //房型信息
            $selects = [
                "product_code", "bed_type", "name", "bed_width", "floor", "max_room",
                "room_size", "window", "people_num", "policy_ban_smoking", "policy_add_bed",
                "policy_breakfast", "json_service_facilitys"
            ];
            $roomDetail = HotelRoom::find()->where([
                "product_code" => $orderDetail['product_code'],
                "hotel_id"     => $orderDetail['hotel_id']
            ])->select($selects)->asArray()->one();
            if(!$roomDetail){
                throw new \Exception("无法获取房型信息");
            }

            $isPayable = OrderHelper::isPayable2($orderDetail['order_status'], $orderDetail['pay_status'], $orderDetail['created_at'], $orderDetail['booking_start_date'], $orderDetail['booking_days']);
            $isCancelable = OrderHelper::isCancelable($orderDetail['order_status'], $orderDetail['pay_status'], $orderDetail['created_at'], $orderDetail['booking_start_date'], $orderDetail['booking_days']);

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'action' => [
                        'is_payable'    => $isPayable ? 1 : 0,
                        'is_cancelable' => $isCancelable ? 1 : 0,
                        'is_refundable' => 0,
                    ],
                    'order' => $orderDetail,
                    'hotel' => $hotelDetail,
                    'room'  => $roomDetail
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