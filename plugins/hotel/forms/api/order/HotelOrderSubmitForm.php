<?php
namespace app\plugins\hotel\forms\api\order;


use app\core\ApiCode;
use app\helpers\CommonHelper;
use app\plugins\hotel\models\HotelOrder;
use app\plugins\hotel\models\HotelPlateforms;

class HotelOrderSubmitForm extends HotelOrderPreviewForm {

    public $arrive_date;
    public $passengers;

    public function rules(){
        return array_merge(parent::rules(), [
            [['arrive_date', 'passengers'], 'required'],
        ]);
    }

    public function save(){
        if(!$this->validate()){
            return $this->responseErrorInfo();
        }

        $trans = \Yii::$app->db->beginTransaction();
        try {

            $this->validateStartDate();

            $this->validatePassengers();

            //获取房型信息
            $room = $this->getRoom();

            //获取酒店信息
            $hotel = $this->getHotel($room);

            $bookingItem = $this->getBookingItem($hotel);

            $hotelPlateform = HotelPlateforms::findOne($bookingItem['hotel_plateform_id']);
            if(!$hotelPlateform){
                throw new \Exception("无法获取酒店平台信息");
            }

            if($this->num > $bookingItem['product_num']){
                throw new \Exception("可预订房间数量不足");
            }

            //计算订单价格
            $orderPrice = $this->days * $this->num * $bookingItem['product_price'];

            //生成订单
            $order = new HotelOrder([
                "mall_id"             => \Yii::$app->mall->id,
                "hotel_id"            => $hotel->id,
                "user_id"             => \Yii::$app->user->id,
                "product_code"        => $this->product_code,
                "unique_id"           => $this->unique_id,
                "order_no"            => "HO" . date("ymdHis") . rand(100, 999),
                "order_status"        => "unpaid",
                "order_price"         => $orderPrice,
                "booking_num"         => $this->num,
                "booking_start_date"  => $this->start_date,
                "booking_days"        => $this->days,
                "real_booking_days"   => $this->days,
                "booking_passengers"  => $this->passengers,
                "booking_arrive_date" => date("Y-m-d H:i:s", strtotime($this->arrive_date)),
                "created_at"          => time(),
                "updated_at"          => time(),
                "pay_status"          => "unpaid",
                "origin_booking_data" => json_encode($bookingItem['origin_data'])
            ]);
            if(!$order->save()){
                throw new \Exception($this->responseErrorMsg($order));
            }

            //关联平台
            $orderPlateform = new HotelPlateforms([
                "mall_id"             => \Yii::$app->mall->id,
                "type"                => "order",
                "source_code"         => $order->order_no,
                "plateform_code"      => $order->order_no,
                "plateform_class"     => $hotelPlateform->plateform_class,
                "plateform_json_data" => "{}"
            ]);
            if(!$orderPlateform->save()){
                throw new \Exception($this->responseErrorMsg($orderPlateform));
            }

            $trans->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data'  => [
                    "order_no"    => $order->order_no,
                    "order_price" => round($order->order_price, 2)
                ]
            ];
        }catch (\Exception $e){
            $trans->rollBack();
            return [
                'code' => ApiCode::CODE_FAIL,
                'msg'  => $e->getMessage()
            ];
        }
    }

    /**
     * 验证入住人信息
     * @throws \Exception
     */
    private function validatePassengers(){
        $passengers = @json_decode($this->passengers, true);
        if(empty($passengers) || !is_array($passengers)){
            throw new \Exception("入住信息信息参数内容JSON格式错误");
        }
        foreach($passengers as $passenger){
            if(empty($passenger['name'])){
                throw new \Exception("姓名不能为空");
            }
            if(empty($passenger['mobile'])){
                throw new \Exception("手机号不能为空");
            }
            if(!CommonHelper::isMobile($passenger['mobile'])){
                throw new \Exception("手机号格式不正确");
            }
        }
    }
}