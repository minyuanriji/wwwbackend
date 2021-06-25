<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\plugins\hotel\libs\bestwehotel\client\booking\PostOrderClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\Passenger;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\PostOrderRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\models\HotelPlateforms;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject {

    public $hotelOrder;
    public $plateform_class;

    public function run(){

        try {
            $hotelPlateformInfo = HotelPlateforms::find()->where([
                'plateform_class' => $this->plateform_class,
                'source_code'     => $this->hotelOrder->hotel_id,
                'type'            => 'hotel'
            ])->select(["plateform_code"])->asArray()->one();
            if(!$hotelPlateformInfo){
                throw new HotelException("无法获取HOTEL ID ".$this->hotelOrder->hotel_id." 平台信息");
            }

            $roomPlateformInfo = HotelPlateforms::find()->where([
                'plateform_class' => $this->plateform_class,
                'source_code'     => $this->hotelOrder->product_code,
                'type'            => 'room'
            ])->select(["plateform_code"])->asArray()->one();
            if(!$roomPlateformInfo){
                throw new HotelException("无法获取ROOM PRODUCT CODE ".$this->hotelOrder->product_code." 平台信息");
            }

            $endDay = date("Y-m-d", strtotime($this->hotelOrder->booking_start_date) + $this->hotelOrder->booking_num * 3600 * 24);
            $bookingData = @json_decode($this->hotelOrder->origin_booking_data, true);

            $requestModel = new PostOrderRequest([
                "innId" => $hotelPlateformInfo['plateform_code'],
                "roomTypeId"  => $roomPlateformInfo['plateform_code'],
                "roomCount"   => $this->hotelOrder->booking_num,
                "dtArrorig"   => $this->hotelOrder->booking_start_date,
                "dtDeporig"   => $endDay,
                "payType"     => 0,
                "totalRate"   => $this->hotelOrder->order_price,
                "externalId"  => $this->hotelOrder->order_no,
                "productCode" => isset($bookingData['productCode']) ? $bookingData['productCode'] : '',
            ]);

            $passengers = @json_decode($this->hotelOrder->booking_passengers, true);
            foreach($passengers as $passenger){
                $requestModel->addPassenger(new Passenger([
                    "guestName"   => $passenger['name'],
                    "guestMobile" => $passenger['mobile']
                ]));
            }

            $res = Request::execute(new PostOrderClient($requestModel));
            print_r($res);
            exit;
        }catch (HotelException $e){
            echo $e->getMessage();
            exit;
        }

    }
}