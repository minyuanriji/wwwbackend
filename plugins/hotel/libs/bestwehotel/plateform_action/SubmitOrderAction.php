<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\helpers\ArrayHelper;
use app\plugins\hotel\libs\bestwehotel\client\booking\PostOrderClient;
use app\plugins\hotel\libs\bestwehotel\client\booking\QueryOrderClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\Passenger;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\PostOrderRequest;
use app\plugins\hotel\libs\bestwehotel\request_model\booking\QueryOrderRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\libs\plateform\SubmitOrderResult;
use app\plugins\hotel\models\HotelPlateforms;
use yii\base\BaseObject;

class SubmitOrderAction extends BaseObject {

    public $hotelOrder;
    public $plateform_class;

    public function run(){

        $submitOrderResult = new SubmitOrderResult();

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

            $endDay = date("Y-m-d", strtotime($this->hotelOrder->booking_start_date) + $this->hotelOrder->booking_days * 3600 * 24);
            $bookingData = @json_decode($this->hotelOrder->origin_booking_data, true);

            //计算到店时间如果在 凌晨4点之前 就等于4点   18-24 之间就等于第二天凌晨4点
            $two_four_time = date('Y-m-d H:i:s', (strtotime(date('Y-m-d',strtotime('+1 day'))) + 14400 - 1));//第二天4点
            $lastArrTim = $this->hotelOrder->booking_arrive_date;
            $booking_arrive_time = strtotime($lastArrTim);//到店时间
            $eighteen_time = strtotime("18:00:00");//当天18点
            $twenty_three_time = strtotime("23:59:59");//当天23点
            $zero_time = strtotime("00:00:00");//当天凌晨
            $four_time = strtotime("04:00:00");//当天4点

            if ($booking_arrive_time >= $zero_time && $booking_arrive_time <= $four_time) {
                $lastArrTim = date('Y-m-d H:i:s', $four_time - 1);
            }

            if ($booking_arrive_time >= $eighteen_time && $twenty_three_time <= $twenty_three_time){
                $lastArrTim = $two_four_time;
            }

            $requestModel = new PostOrderRequest([
                "innId" => $hotelPlateformInfo['plateform_code'],
                "roomTypeId"   => $roomPlateformInfo['plateform_code'],
                "roomCount"    => $this->hotelOrder->booking_num,
                "dtArrorig"    => $this->hotelOrder->booking_start_date,
                "dtDeporig"    => $endDay,
                "payType"      => 1,
                "totalRate"    => $this->hotelOrder->order_price,
                "externalId"   => $this->hotelOrder->order_no,
                "productCode"  => isset($bookingData['productCode']) ? $bookingData['productCode'] : '',
                "lastArrTime"  => $lastArrTim,
            ]);

            $passengers = @json_decode($this->hotelOrder->booking_passengers, true);
            foreach($passengers as $passenger){
                $requestModel->addPassenger(new Passenger([
                    "guestName"   => $passenger['name'],
                    "guestMobile" => $passenger['mobile']
                ]));
            }

            $response = Request::execute(new PostOrderClient($requestModel));
            if(!$response instanceof HotelResponse){
                throw new HotelException("结果对象返回类型[HotelResponse]错误");
            }

            if($response->code != HotelResponse::CODE_SUCC){
                $resultData = @json_decode($response->result_content, true);
                if(isset($resultData['msgCode']) && $resultData['msgCode'] == -10224){ //重复下单 改查询
                    //查询订单
                    $requestModel = new QueryOrderRequest([
                        "externalId" => $this->hotelOrder->order_no
                    ]);
                    $response = Request::execute(new QueryOrderClient($requestModel));
                    if(!$response instanceof HotelResponse){
                        throw new HotelException("结果对象返回类型[HotelResponse]错误");
                    }
                    $responseModel = $response->responseModel;
                    $orderNo = $responseModel->orderCode;
                    $originData = ArrayHelper::toArray($responseModel);
                }else{
                    throw new HotelException($response->error);
                }
            }else{
                $responseModel = $response->responseModel;
                $orderNo = $responseModel->orderCode;
                $originData = ArrayHelper::toArray($responseModel);
            }

            $submitOrderResult->plateform_order_no = $orderNo;
            $submitOrderResult->originData         = $originData;
            $submitOrderResult->code               = SubmitOrderResult::CODE_SUCC;

        }catch (HotelException $e){
            $submitOrderResult->code = SubmitOrderResult::CODE_FAIL;
            $submitOrderResult->message = $e->getMessage();
        }

        return $submitOrderResult;
    }
}