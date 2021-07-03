<?php
namespace app\plugins\hotel\libs\bestwehotel\plateform_action;


use app\helpers\ArrayHelper;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelInfoClient;
use app\plugins\hotel\libs\bestwehotel\client\hotel\GetHotelRoomStatusClient;
use app\plugins\hotel\libs\bestwehotel\Request;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelInfoRequest;
use app\plugins\hotel\libs\bestwehotel\request_model\hotel\GetHotelRoomStatusRequest;
use app\plugins\hotel\libs\HotelException;
use app\plugins\hotel\libs\HotelResponse;
use app\plugins\hotel\models\Hotels;
use yii\base\BaseObject;

class UpdateAction extends BaseObject {

    public $hotel;
    public $hotelPlateform;

    public function run(){

        try {
            //获取酒店信息
            $requestModel = new GetHotelInfoRequest([
                "innId"  => $this->hotelPlateform->plateform_code
            ]);
            $client = new GetHotelInfoClient($requestModel);
            $response = Request::execute($client);
            if($response->code != HotelResponse::CODE_SUCC){
                throw new HotelException($response->error);
            }

            $hotelInfo = ArrayHelper::toArray($response->responseModel);

            $this->setHotelPrice($this->hotel, $hotelInfo);

        }catch (HotelException $e){
            throw $e;
        }

    }

    /**
     * 设置酒店价格
     * @param Hotels $hotel
     * @param array $info
     */
    private function setHotelPrice(Hotels $hotel, $info){
        $requestModel = new GetHotelRoomStatusRequest([
            "innId"    => $info['innId'],
            "endOfDay" => date("Y-m-d"),
            "days"     => 1
        ]);
        $client = new GetHotelRoomStatusClient($requestModel);
        $result = Request::execute($client);
        if($result instanceof HotelResponse){
            $minPrice = 0;
            if($result->responseModel->roomTypeList){
                foreach($result->responseModel->roomTypeList as $roomTypeList){
                    if($roomTypeList->productList){
                        foreach($roomTypeList->productList as $productData){
                            if(!$minPrice){
                                $minPrice = $productData->advanceRate;
                            }elseif($productData->advanceRate){
                                $minPrice = min($minPrice, $productData->advanceRate);
                            }
                        }
                    }
                }
            }

            $hotel->price      = $minPrice;
            $hotel->updated_at = time();

            if(!$hotel->save()){
                throw new HotelException(json_encode($hotel->getErrors()));
            }
        }
    }
}