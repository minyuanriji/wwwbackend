<?php
namespace app\plugins\hotel\libs\bestwehotel\client\hotel;


use app\plugins\hotel\libs\bestwehotel\client\BaseClient;
use app\plugins\hotel\libs\bestwehotel\client\IClient;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelInfo\HotelGetHotelInfoMapInfoItemModel;
use app\plugins\hotel\libs\bestwehotel\response_model\HotelGetHotelInfo\HotelGetHotelInfoResponseModel;
use app\plugins\hotel\libs\HotelException;

class GetHotelInfoClient extends BaseClient implements IClient {

    public function parseResponseModel($parseArray){
        if(!isset($parseArray['result'])){
            throw new HotelException("[HotelGetHotelInfoClient::parseResponseModel]解析数据错误");
        }
        $responseModel = HotelGetHotelInfoResponseModel::create($parseArray['result']);
        if(isset($parseArray['result']['mapInfo']) && is_array($parseArray['result']['mapInfo'])){
            foreach($parseArray['result']['mapInfo'] as $map){
                $responseModel->addMapInfo(new HotelGetHotelInfoMapInfoItemModel([
                    "lag"     => $map['lag'],
                    "lng"     => $map['lng'],
                    "mapType" => $map['mapType']
                ]));
            }
        }
        return $responseModel;
    }
}